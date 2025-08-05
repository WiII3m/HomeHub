import { describe, it, expect, beforeEach, vi } from 'vitest'
import fs from 'fs'
import path from 'path'

describe('Generate & render html dom', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    delete window.DashboardCameras
    delete window.selectCameraWithCurrentState
    delete window.showCameraCards
    delete window.toggleCameraPrivacy
    delete window.startCameraStream
    delete window.controlCameraPtz

    const widgetPath = path.resolve('./app/Widgets/Core/resources/js/widget.js')
    const widgetContent = fs.readFileSync(widgetPath, 'utf-8')
    eval(widgetContent)

    const polyfillPath = path.resolve('./app/Widgets/Core/resources/js/polyfills.js')
    const polyfillContent = fs.readFileSync(polyfillPath, 'utf-8')
    eval(polyfillContent)

    const localStorageMock = {
      getItem: vi.fn(),
      setItem: vi.fn(),
    }
    Object.defineProperty(window, 'localStorage', {
      value: localStorageMock,
      writable: true
    })

    window.camerasState = {
      'bfddac4d500c3243d9nhzw': {
        id: 'bfddac4d500c3243d9nhzw',
        name: 'Rez de chaussé',
        online: false,
        basic_private: ''
      },
      'bf69c655d66cd06c74qjgg': {
        id: 'bf69c655d66cd06c74qjgg',
        name: 'Chambre enfants',
        online: true,
        basic_private: 1
      }
    }

    window.camerasWidgetConfig = {
      icon: '📹',
      title: 'Caméras'
    }

    const scriptPath = path.resolve('./app/Widgets/Cameras/resources/js/cameras.js')
    const scriptContent = fs.readFileSync(scriptPath, 'utf-8')

    eval(scriptContent)
    expect(window.DashboardCameras).toBeDefined()
    expect(window.Widget).toBeDefined()
    expect(window.PolyfillUtils).toBeDefined()
  })

  it('should create camera card on init', () => {
    document.body.innerHTML = `
      <div class="panel panel-default">
        <div class="panel-body">
          <div id="cameras-dynamic-container"></div>
        </div>
      </div>
    `
    window.DashboardCameras.init()

    const container = document.getElementById('cameras-dynamic-container')

    // Assert - Header avec config
    expect(container.innerHTML).toContain('📹') // icon
    expect(container.innerHTML).toContain('Caméras') // title

    // Assert - Structure et contenu des cartes
    expect(container.innerHTML).toContain('data-camera-id=')

    // Assert - États des caméras
    // Caméra OFFLINE avec mode privé (Rez de chaussé) - online: false, basic_private: 1
    expect(container.innerHTML).toContain('data-camera-id="bf69c655d66cd06c74qjgg')
    expect(container.innerHTML).toContain('Rez de chaussé')
    expect(container.innerHTML).toContain('Hors ligne')
    expect(container.innerHTML).toContain('status-point offline')

    // Caméra ONLINE sans mode privé (Chambre enfants) - online: true, basic_private: ''
    expect(container.innerHTML).toContain('data-camera-id="bfddac4d500c3243d9nhzw')
    expect(container.innerHTML).toContain('Chambre enfants')
    expect(container.innerHTML).toContain('En ligne')
    expect(container.innerHTML).toContain('status-point online')
    expect(container.innerHTML).toContain('Mode privé activé')

  })

  it('should toggle synthetic/single view', () => {
    document.body.innerHTML = `<div id="cameras-dynamic-container"></div>`
    window.DashboardCameras.init()

    // Act 1 - Sélectionner une caméra via la fonction globale
    window.selectCameraWithCurrentState('bf69c655d66cd06c74qjgg', 'Chambre enfants')

    // Assert 1 - Vérifier la vue détaillée
    let container = document.getElementById('cameras-dynamic-container')
    expect(container.innerHTML).toContain('camera-detail-view')
    expect(container.innerHTML).toContain('Chambre enfants')
    expect(container.innerHTML).toContain('Retour à la liste')
    expect(container.innerHTML).toContain('camera-video-container')
    expect(container.innerHTML).toContain('ptz-controls')
    expect(container.innerHTML).toContain('ptz-button')

    // Act 2 - Retour à la liste via la fonction globale
    window.showCameraCards()

    // Assert 2 - Vérifier qu'on est revenu aux cartes
    container = document.getElementById('cameras-dynamic-container')
    expect(container.innerHTML).toContain('data-camera-id=')
    expect(container.innerHTML).not.toContain('camera-detail-view')
    expect(container.innerHTML).toContain('data-camera-id="bf69c655d66cd06c74qjgg')
    expect(container.innerHTML).toContain('data-camera-id="bfddac4d500c3243d9nhzw')

  })

  it('should handle state changes', () => {
    document.body.innerHTML = `<div id="cameras-dynamic-container"></div>`
    window.DashboardCameras.init()

    const container = document.getElementById('cameras-dynamic-container')
    expect(container.innerHTML).toContain('data-camera-id="bfddac4d500c3243d9nhzw"')

    // Simuler un changement d'état
    window.DashboardCameras.updateCameraStatus('bfddac4d500c3243d9nhzw', { online: true })

    const el = document.querySelectorAll('[data-camera-id="bfddac4d500c3243d9nhzw"]');
    expect(el.length == 1).toBeTruthy()
    expect(el[0].innerHTML).toContain('online')

  })
})
