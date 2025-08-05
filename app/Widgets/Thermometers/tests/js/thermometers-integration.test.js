import { describe, it, expect, beforeEach, vi } from 'vitest'
import fs from 'fs'
import path from 'path'

describe('Generate & render html dom', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    delete window.DashboardThermometers
    delete window.showThermometerHistory
    delete window.changeTemperaturePeriod
    delete window.showThermometersCards
    delete window.openThermometersSettingsModal

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
      get: vi.fn().mockResolvedValue({
        success: true,
        data: [
          { hour: '2024-01-01 12:00', temperature: 22.5, humidity: 45, heat_index: 23.2 },
          { hour: '2024-01-01 13:00', temperature: 23.1, humidity: 43, heat_index: 24.0 }
        ]
      })
    }

    // Mock DashboardModal
    window.DashboardModal = {
      open: vi.fn()
    }

    window.thermometersState = {
      'bfdb6ddbf1877a9d68q3jb': {
        id: 'bfdb6ddbf1877a9d68q3jb',
        name: 'Salon',
        online: true,
        temperature: 22.5,
        humidity: 48,
        heat_index: 23.2,
        battery: 82,
        temperature_color: '#FF6B6B',
        humidity_color: '#4ECDC4',
        thermal_emoji: '😌'
      },
      'bf5c8d9af2b66c4e85x7yz': {
        id: 'bf5c8d9af2b66c4e85x7yz',
        name: 'Chambre',
        online: false,
        temperature: null,
        humidity: null,
        heat_index: null,
        battery: null,
        temperature_color: '#999999',
        humidity_color: '#999999',
        thermal_emoji: '❓'
      }
    }

    window.thermometersWidgetConfig = {
      icon: '🌡️',
      title: 'Température'
    }

    const scriptPath = path.resolve('./app/Widgets/Thermometers/resources/js/thermometers.js')
    const scriptContent = fs.readFileSync(scriptPath, 'utf-8')

    eval(scriptContent)
    expect(window.DashboardThermometers).toBeDefined()
    expect(window.Widget).toBeDefined()
    expect(window.PolyfillUtils).toBeDefined()
  })

  it('should create thermometer cards on init', () => {
    document.body.innerHTML = `
      <div class="panel panel-default">
        <div class="panel-body">
          <div id="thermometers-dynamic-container"></div>
        </div>
      </div>
    `
    window.DashboardThermometers.init()

    const container = document.getElementById('thermometers-dynamic-container')

    // Assert - Header avec config
    expect(container.innerHTML).toContain('🌡️') // icon
    expect(container.innerHTML).toContain('Température') // title

    // Assert - Structure et contenu des cartes
    expect(container.innerHTML).toContain('data-thermometer-card')
    expect(container.innerHTML).toContain('data-thermometers-list')

    // Assert - Thermomètres
    expect(container.innerHTML).toContain('data-device-id="bfdb6ddbf1877a9d68q3jb"')
    expect(container.innerHTML).toContain('data-device-id="bf5c8d9af2b66c4e85x7yz"')

    // Assert - Noms des thermomètres
    expect(container.innerHTML).toContain('Salon')
    expect(container.innerHTML).toContain('Chambre')

    // Assert - États des thermomètres
    // Salon: online=true avec données
    expect(container.querySelector('[data-device-id="bfdb6ddbf1877a9d68q3jb"] .status-point')).toHaveClass('online')
    
    // Le contenu "En ligne" est bien présent dans la carte
    const salonCard = container.querySelector('[data-device-id="bfdb6ddbf1877a9d68q3jb"]')
    expect(salonCard.innerHTML).toContain('En ligne')
    expect(container.innerHTML).toContain('22.5°C') // température
    expect(container.innerHTML).toContain('48%') // humidité
    expect(container.innerHTML).toContain('23.2°C') // heat index
    expect(container.innerHTML).toContain('82%') // batterie
    expect(container.innerHTML).toContain('😌') // emoji thermique

    // Chambre: online=false sans données
    expect(container.querySelector('[data-device-id="bf5c8d9af2b66c4e85x7yz"] .status-point')).toHaveClass('offline')
    const chambreCard = container.querySelector('[data-device-id="bf5c8d9af2b66c4e85x7yz"]')
    expect(chambreCard.innerHTML).toContain('Hors ligne')
    expect(container.innerHTML).toContain('--°C') // pas de température
    expect(container.innerHTML).toContain('--%') // pas d'humidité
    expect(container.innerHTML).toContain('❓') // emoji d'erreur
  })

  it('should navigate to thermometer history view', () => {
    document.body.innerHTML = `<div id="thermometers-dynamic-container"></div>`
    window.DashboardThermometers.init()

    // Act - Cliquer sur un thermomètre pour voir l'historique
    window.showThermometerHistory('bfdb6ddbf1877a9d68q3jb', 'Salon')

    const container = document.getElementById('thermometers-dynamic-container')

    // Assert - Vue d'historique affichée
    expect(container.innerHTML).toContain('data-back-button')
    expect(container.innerHTML).toContain('Retour à la liste')
    expect(container.innerHTML).toContain('Salon') // nom du thermomètre
    expect(container.innerHTML).toContain('Chargement de l\'historique') // loader initial

    // Assert - Appel API pour récupérer l'historique
    expect(window.HttpClient.get).toHaveBeenCalledWith('/widgets/thermometers/history?device_id=bfdb6ddbf1877a9d68q3jb&days=1')
  })

  it('should navigate back to thermometers list', () => {
    document.body.innerHTML = `<div id="thermometers-dynamic-container"></div>`
    window.DashboardThermometers.init()

    // Act 1 - Aller à l'historique
    window.showThermometerHistory('bfdb6ddbf1877a9d68q3jb', 'Salon')
    let container = document.getElementById('thermometers-dynamic-container')
    expect(container.innerHTML).toContain('data-back-button')

    // Act 2 - Retourner à la liste
    window.showThermometersCards()

    // Assert - Vue liste restaurée
    container = document.getElementById('thermometers-dynamic-container')
    expect(container.innerHTML).toContain('data-thermometers-list')
    expect(container.innerHTML).toContain('data-thermometer-card')
    expect(container.innerHTML).not.toContain('temperature-history-container')
    expect(container.innerHTML).toContain('data-device-id="bfdb6ddbf1877a9d68q3jb"')
    expect(container.innerHTML).toContain('data-device-id="bf5c8d9af2b66c4e85x7yz"')
  })

  it('should change temperature history period', async () => {
    document.body.innerHTML = `<div id="thermometers-dynamic-container"></div>`
    window.DashboardThermometers.init()

    // Act 1 - Aller à l'historique
    window.showThermometerHistory('bfdb6ddbf1877a9d68q3jb', 'Salon')

    // Act 2 - Changer la période à 7 jours
    window.changeTemperaturePeriod(7)

    // Assert - Nouvel appel API avec 7 jours
    expect(window.HttpClient.get).toHaveBeenCalledWith('/widgets/thermometers/history?device_id=bfdb6ddbf1877a9d68q3jb&days=7')
  })

  it('should handle missing thermometer data gracefully', () => {
    // Arrange - État vide
    window.thermometersState = {}

    document.body.innerHTML = `<div id="thermometers-dynamic-container"></div>`

    // Act & Assert - Pas d'erreur avec état vide
    expect(() => {
      window.DashboardThermometers.init()
    }).not.toThrow()

    const container = document.getElementById('thermometers-dynamic-container')
    expect(container).toBeTruthy()
  })

  it('should open settings modal', () => {
    document.body.innerHTML = `<div id="thermometers-dynamic-container"></div>`
    window.DashboardThermometers.init()

    // Act - Ouvrir le modal de paramètres
    window.openThermometersSettingsModal()

    // Assert - DashboardModal.open appelé
    expect(window.DashboardModal.open).toHaveBeenCalledWith(
      'thermometersSettingsModal',
      expect.any(Function)
    )
  })
})
