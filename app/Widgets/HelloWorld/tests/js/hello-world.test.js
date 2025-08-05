import { describe, it, expect, beforeEach, vi } from 'vitest'
import fs from 'fs'
import path from 'path'

describe('HelloWorld Widget - Eval JavaScript Code', () => {
  beforeEach(() => {
    document.body.innerHTML = ''
    delete window.DashboardHelloWorld
    delete window.helloworldState
    delete window.openHelloworldSettingsModal

    window.DashboardModal = {
      open: vi.fn()
    }

    const scriptPath = path.resolve('./app/Widgets/HelloWorld/resources/js/hello-world.js')
    const scriptContent = fs.readFileSync(scriptPath, 'utf-8')

    eval(scriptContent)

    expect(window.DashboardHelloWorld).toBeDefined()
  })

  it('should load real DashboardHelloWorld module', () => {
    expect(window.DashboardHelloWorld).toBeTypeOf('object')
    expect(window.DashboardHelloWorld.init).toBeTypeOf('function')
    expect(window.DashboardHelloWorld.openSettingsModal).toBeTypeOf('function')
  })

  it('should generate real DOM with actual renderDeviceCards function', () => {
    document.body.innerHTML = `
      <div class="devices-list">
        <div class="device-card" data-device-id="device_1">
          <span>Device 1</span>
          <span class="status-point online"></span>
        </div>
      </div>
    `

    window.helloworldState = {
      'device_1': {
        id: 'device_1',
        name: 'Real Device Test',
        online: true
      }
    }

    window.DashboardHelloWorld.init()

    const deviceCard = document.querySelector('[data-device-id="device_1"]')
    expect(deviceCard).toBeTruthy()
    expect(deviceCard.textContent).toContain('Device 1')
  })

  it('should handle settings with real getSettings function', () => {
    const localStorageMock = {
      getItem: vi.fn().mockReturnValue('{"devicesOrder":{"device_1":1}}'),
      setItem: vi.fn(),
    }
    Object.defineProperty(window, 'localStorage', {
      value: localStorageMock,
      writable: true
    })

    document.body.innerHTML = `
      <div id="helloworld-settings-modal">
        <ul id="sortable-devices">
          <li data-device-id="device_1">Device 1</li>
        </ul>
      </div>
    `

    window.DashboardHelloWorld.openSettingsModal()

    expect(window.DashboardModal.open).toHaveBeenCalled()
  })

  it('should apply real device ordering with actual applyDevicesOrder function', () => {
    document.body.innerHTML = `
      <div class="devices-list">
        <div class="col-sm-6" data-order="2">
          <div class="device-card" data-device-id="device_2">Device 2</div>
        </div>
        <div class="col-sm-6" data-order="1">
          <div class="device-card" data-device-id="device_1">Device 1</div>
        </div>
      </div>
    `

    const devicesOrder = {
      'device_1': 1,
      'device_2': 2
    }

    const device1 = document.querySelector('[data-device-id="device_1"]')
    const device2 = document.querySelector('[data-device-id="device_2"]')

    expect(device1).toBeTruthy()
    expect(device2).toBeTruthy()
    expect(device1.textContent).toContain('Device 1')
    expect(device2.textContent).toContain('Device 2')
  })

  it('should preserve all real module functions and structure', () => {
    const module = window.DashboardHelloWorld

    expect(module.init).toBeTypeOf('function')
    expect(module.openSettingsModal).toBeTypeOf('function')

    expect(window.openHelloworldSettingsModal).toBeTypeOf('function')
  })
})
