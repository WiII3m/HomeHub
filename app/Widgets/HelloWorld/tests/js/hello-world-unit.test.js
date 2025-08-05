import { describe, it, expect, beforeEach, vi } from 'vitest'

const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
}

function getSettings() {
  try {
    var settings = localStorage.getItem('helloworld-settings');
    return settings ? JSON.parse(settings) : {
      devicesOrder: {}
    };
  } catch (e) {
    return {
      devicesOrder: {}
    };
  }
}

describe('HelloWorld Widget - Data Functions', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    global.localStorage = localStorageMock
  })

  describe('getSettings()', () => {
    it('should return default settings when localStorage is empty', () => {
      localStorageMock.getItem.mockReturnValue(null)

      const result = getSettings()

      expect(result).toEqual({
        devicesOrder: {}
      })
      expect(localStorageMock.getItem).toHaveBeenCalledWith('helloworld-settings')
    })

    it('should parse and return valid localStorage data', () => {
      const mockData = {
        devicesOrder: {
          'device_1': 1,
          'device_2': 2
        }
      }
      localStorageMock.getItem.mockReturnValue(JSON.stringify(mockData))

      const result = getSettings()

      expect(result).toEqual(mockData)
      expect(result.devicesOrder).toHaveProperty('device_1', 1)
      expect(result.devicesOrder).toHaveProperty('device_2', 2)
    })

    it('should return default settings when localStorage data is corrupted', () => {
      localStorageMock.getItem.mockReturnValue('{"invalid":json}')

      const result = getSettings()

      expect(result).toEqual({
        devicesOrder: {}
      })
    })

    it('should have correct data structure', () => {
      localStorageMock.getItem.mockReturnValue(null)

      const result = getSettings()

      expect(result).toBeTypeOf('object')
      expect(result).toHaveProperty('devicesOrder')
      expect(result.devicesOrder).toBeTypeOf('object')
    })

    it('should handle complex devicesOrder data', () => {
      const complexData = {
        devicesOrder: {
          'device_abc123': 1,
          'device_xyz789': 2,
          'device_special-chars': 3
        }
      }
      localStorageMock.getItem.mockReturnValue(JSON.stringify(complexData))

      const result = getSettings()

      expect(Object.keys(result.devicesOrder)).toHaveLength(3)
      expect(result.devicesOrder['device_abc123']).toBe(1)
      expect(result.devicesOrder['device_special-chars']).toBe(3)
    })
  })
})
