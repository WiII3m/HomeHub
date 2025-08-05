import { describe, it, expect, beforeEach, vi } from 'vitest'
import fs from 'fs'
import path from 'path'

describe('Generate & render html dom', () => {
  beforeEach(() => {
    document.body.innerHTML = `
        <div data-scenes-container>
        <div>
            <div data-scene-id="scene_001">
                <span data-scene-name>Salon</span>
            </div>
        </div>
        <div>
            <div data-scene-id="scene_002">
                <span data-scene-name>Cuisine</span>
            </div>
        </div>
        </div>
    `

    const widgetPath = path.resolve('./app/Widgets/Core/resources/js/widget.js')
    const widgetContent = fs.readFileSync(widgetPath, 'utf-8')
    eval(widgetContent)

    const polyfillPath = path.resolve('./app/Widgets/Core/resources/js/polyfills.js')
    const polyfillContent = fs.readFileSync(polyfillPath, 'utf-8')
    eval(polyfillContent)

    const localStorageMock = {
        getItem: vi.fn().mockReturnValue('{"scenesOrder":{"scene_002":1,"scene_001":2}}'),
        setItem: vi.fn(),
    }
    Object.defineProperty(window, 'localStorage', {
      value: localStorageMock,
      writable: true
    })

    // Mock HttpClient pour éviter les appels réseau
    window.HttpClient = {
      post: vi.fn().mockResolvedValue({ success: true })
    }

    // Mock DashboardUtils & DashboardModal
    window.DashboardUtils = {
      showStatusMessage: vi.fn()
    }

    window.DashboardModal = {
      open: vi.fn()
    }

    const scriptPath = path.resolve('./app/Widgets/Scenes/resources/js/scenes.js')
    const scriptContent = fs.readFileSync(scriptPath, 'utf-8')

    eval(scriptContent)
    expect(window.DashboardScenes).toBeDefined()
    expect(window.Widget).toBeDefined()
    expect(window.PolyfillUtils).toBeDefined()

    window.DashboardScenes.init()
  })

  it('should get settings & order scenes on init', () => {
    const sceneButtons = document.querySelectorAll('[data-scene-id]')
    expect(sceneButtons).toHaveLength(2)
    expect(sceneButtons[0].getAttribute('data-scene-id')).toBe('scene_002')
    expect(sceneButtons[1].getAttribute('data-scene-id')).toBe('scene_001')
  })

  it('should trigger scene when button is clicked', async() => {
    const sceneButton = document.querySelector('[data-scene-id="scene_001"]')

    sceneButton.click()

    expect(window.HttpClient.post).toHaveBeenCalledWith('/widgets/scenes/trigger/scene_001', {
      scene_id: 'scene_001'
    })

    await vi.waitFor(() => {
        expect(window.DashboardUtils.showStatusMessage).toHaveBeenCalledWith('Scène activée avec succès', 'success')
        expect(sceneButton).toHaveClass('success')
    })
  })

  it('should handle scene activation failure', async () => {
    window.HttpClient.post = vi.fn().mockResolvedValue({ success: false, error: 'api_response' })

    const sceneButton = document.querySelector('[data-scene-id="scene_001"]')

    window.DashboardScenes.triggerScene('scene_001', sceneButton)

    await vi.waitFor(() => {
        expect(window.DashboardUtils.showStatusMessage).toHaveBeenCalledWith('Erreur: api_response', 'error')
    })
  })

  it('should handle network error', async () => {
    window.HttpClient.post = vi.fn().mockRejectedValue(new Error('Network error'))

    const sceneButton = document.querySelector('[data-scene-id="scene_001"]')
    window.DashboardScenes.triggerScene('scene_001', sceneButton)

    await vi.waitFor(() => {
        expect(window.DashboardUtils.showStatusMessage).toHaveBeenCalledWith('Erreur: Network error', 'error')
    })
  })

  it('should handle missing scene ID', () => {
    window.DashboardScenes.triggerScene('', null)

    expect(window.DashboardUtils.showStatusMessage).toHaveBeenCalledWith('ID de scène manquant', 'error')
    expect(window.HttpClient.post).not.toHaveBeenCalled()
  })

  it('should open settings modal', () => {
    window.DashboardScenes.init()

    window.openScenesSettingsModal()

    // Assert - DashboardModal.open appelé
    expect(window.DashboardModal.open).toHaveBeenCalledWith(
      'scenesSettingsModal',
      expect.any(Function)
    )
  })

  it('should handle scenes without DOM elements gracefully', () => {
    document.body.innerHTML = `
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="row" data-scenes-container>
        </div>
      </div>
    `

    expect(() => {
      window.DashboardScenes.init()
    }).not.toThrow()
  })
})
