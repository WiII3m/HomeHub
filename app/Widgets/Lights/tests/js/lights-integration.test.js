import { describe, it, expect, beforeEach, vi } from 'vitest'
import fs from 'fs'
import path from 'path'

describe('Generate & render html dom', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    delete window.DashboardLights
    delete window.toggleLight
    delete window.toggleLightFromCard
    delete window.openLightsSettingsModal

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

    // Mock HttpClient pour éviter les appels réseau
    window.HttpClient = {
      post: vi.fn().mockResolvedValue({ success: true })
    }

    window.lightsState = [
      {
        room_id: 'salon',
        room_name: 'Salon',
        lights: [
          {
            id: 'bf4c7e5a123456789abc01',
            name: 'Lampe principale',
            online: true,
            switch_state: false
          },
          {
            id: 'bf4c7e5a123456789abc02',
            name: 'Lampe d\'appoint',
            online: false,
            switch_state: true
          }
        ]
      },
      {
        room_id: 'cuisine',
        room_name: 'Cuisine',
        lights: [
          {
            id: 'bf4c7e5a123456789abc03',
            name: 'Éclairage plan travail',
            online: true,
            switch_state: true
          }
        ]
      }
    ]

    const scriptPath = path.resolve('./app/Widgets/Lights/resources/js/lights.js')
    const scriptContent = fs.readFileSync(scriptPath, 'utf-8')

    eval(scriptContent)
    expect(window.DashboardLights).toBeDefined()
    expect(window.Widget).toBeDefined()
    expect(window.PolyfillUtils).toBeDefined()
  })

  it('should create room sections and light cards on init', () => {
    document.body.innerHTML = `
      <div class="panel panel-default">
        <div class="panel-body">
          <div id="lights-dynamic-container"></div>
        </div>
      </div>
    `
    window.DashboardLights.init()

    const container = document.getElementById('lights-dynamic-container')

    // Assert - Structure générale
    expect(container.innerHTML).toContain('lights-list-view')
    expect(container.innerHTML).toContain('data-room-section')

    // Assert - Sections des pièces
    expect(container.innerHTML).toContain('Salon')
    expect(container.innerHTML).toContain('Cuisine')

    // Assert - Cartes des lumières
    expect(container.innerHTML).toContain('light-item')
    expect(container.innerHTML).toContain('data-device-id="bf4c7e5a123456789abc01"')
    expect(container.innerHTML).toContain('data-device-id="bf4c7e5a123456789abc02"')
    expect(container.innerHTML).toContain('data-device-id="bf4c7e5a123456789abc03"')

    // Assert - Noms des lumières
    expect(container.innerHTML).toContain('Lampe principale')
    expect(container.innerHTML).toContain('Lampe d\'appoint')
    expect(container.innerHTML).toContain('Éclairage plan travail')

    // Assert - États des lumières
    // Lampe principale: online=true, switch_state=false
    expect(container.querySelector('[data-device-id="bf4c7e5a123456789abc01"] .status-point')).toHaveClass('online')
    expect(container.querySelector('[data-device-id="bf4c7e5a123456789abc01"] #light-status-text-bf4c7e5a123456789abc01')).toHaveTextContent('En ligne')
    expect(container.querySelector('#light-switch-bf4c7e5a123456789abc01')).not.toBeChecked()
    expect(container.querySelector('#light-switch-bf4c7e5a123456789abc01')).not.toBeDisabled()

    // Lampe d'appoint: online=false, switch_state=true
    expect(container.querySelector('[data-device-id="bf4c7e5a123456789abc02"] .status-point')).toHaveClass('offline')
    expect(container.querySelector('[data-device-id="bf4c7e5a123456789abc02"] #light-status-text-bf4c7e5a123456789abc02')).toHaveTextContent('Hors ligne')
    expect(container.querySelector('#light-switch-bf4c7e5a123456789abc02')).toBeDisabled()
    expect(container.querySelector('[data-device-id="bf4c7e5a123456789abc02"]')).toHaveClass('opacity-06')

    // Éclairage plan travail: online=true, switch_state=true
    expect(container.querySelector('[data-device-id="bf4c7e5a123456789abc03"] .status-point')).toHaveClass('online')
    expect(container.querySelector('#light-switch-bf4c7e5a123456789abc03')).toBeChecked()
    expect(container.querySelector('#light-switch-bf4c7e5a123456789abc03')).not.toBeDisabled()
  })

  it('should handle toggle light functionality', async () => {
    document.body.innerHTML = `<div id="lights-dynamic-container"></div>`
    window.DashboardLights.init()

    const container = document.getElementById('lights-dynamic-container')
    const lightSwitch = container.querySelector('#light-switch-bf4c7e5a123456789abc01')

    // Act - Simuler un clic sur le switch
    expect(lightSwitch).not.toBeChecked()

    // Simuler le changement de state via toggleLight
    window.toggleLight('bf4c7e5a123456789abc01', true)

    // Assert - Vérifier que HttpClient.post a été appelé
    expect(window.HttpClient.post).toHaveBeenCalledWith('/widgets/lights/toggle', {
      device_id: 'bf4c7e5a123456789abc01',
      state: true
    })
  })

  it('should handle toggle from card functionality', () => {
    document.body.innerHTML = `<div id="lights-dynamic-container"></div>`
    window.DashboardLights.init()

    const container = document.getElementById('lights-dynamic-container')
    const lightSwitch = container.querySelector('#light-switch-bf4c7e5a123456789abc01')

    // Act - Simuler un clic sur la carte (via toggleLightFromCard)
    expect(lightSwitch).not.toBeChecked()

    window.toggleLightFromCard('bf4c7e5a123456789abc01')

    // Assert - Le switch doit être coché maintenant
    expect(lightSwitch).toBeChecked()

    // Assert - HttpClient.post doit avoir été appelé
    expect(window.HttpClient.post).toHaveBeenCalledWith('/widgets/lights/toggle', {
      device_id: 'bf4c7e5a123456789abc01',
      state: true
    })
  })

  it('should not toggle disabled lights', () => {
    document.body.innerHTML = `<div id="lights-dynamic-container"></div>`
    window.DashboardLights.init()

    // Act - Essayer de toggler une lumière hors ligne (disabled)
    window.toggleLightFromCard('bf4c7e5a123456789abc02')

    // Assert - HttpClient.post ne doit PAS avoir été appelé car la lumière est disabled
    expect(window.HttpClient.post).not.toHaveBeenCalled()
  })
})
