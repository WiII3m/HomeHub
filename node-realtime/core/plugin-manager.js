import fs from 'fs';
import path from 'path';

class PluginManager {
  constructor() {
    this.middlewares = [];
    this.loadedPlugins = new Map();
  }

  /**
   * Charger la configuration des plugins depuis Laravel
   */
  async loadFromConfig(configPath) {
    try {
      console.log(`📦 Loading plugins configuration from: ${configPath}`);
      
      if (!fs.existsSync(configPath)) {
        console.log('⚠️ No plugin configuration found, running without plugins');
        return;
      }

      const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
      
      if (config.plugins && Array.isArray(config.plugins)) {
        // Trier par priorité (plus élevée = traité en premier)
        const sortedPlugins = config.plugins.sort((a, b) => (b.priority || 0) - (a.priority || 0));
        
        for (const pluginConfig of sortedPlugins) {
          if (pluginConfig.enabled) {
            await this.loadPlugin(pluginConfig);
          }
        }
      }

      console.log(`✅ Loaded ${this.middlewares.length} plugin middleware(s)`);
      
    } catch (error) {
      console.error('❌ Error loading plugin configuration:', error.message);
    }
  }

  /**
   * Charger un plugin spécifique
   */
  async loadPlugin(pluginConfig) {
    try {
      console.log(`📦 Loading plugin: ${pluginConfig.name}`);
      
      const middlewarePath = pluginConfig.middleware_path;
      
      if (!fs.existsSync(middlewarePath)) {
        console.error(`❌ Middleware file not found: ${middlewarePath}`);
        return;
      }

      // Import du middleware (convertir le chemin en URL pour import())
      const middlewareModule = await import(`file://${middlewarePath}`);
      const MiddlewareClass = middlewareModule.default;
      const middleware = new MiddlewareClass(pluginConfig);
      
      // Vérifier que le middleware a les bonnes méthodes
      if (typeof middleware.canProcess !== 'function' || typeof middleware.process !== 'function') {
        console.error(`❌ Invalid middleware: ${pluginConfig.name} (missing canProcess or process method)`);
        return;
      }

      this.middlewares.push({
        name: pluginConfig.name,
        priority: pluginConfig.priority || 0,
        middleware: middleware,
        config: pluginConfig
      });

      this.loadedPlugins.set(pluginConfig.name, {
        middleware: middleware,
        config: pluginConfig
      });

      console.log(`✅ Plugin loaded: ${pluginConfig.name} (priority: ${pluginConfig.priority || 0})`);
      
    } catch (error) {
      console.error(`❌ Error loading plugin ${pluginConfig.name}:`, error.message);
    }
  }

  /**
   * Traiter les données à travers la chaîne de middlewares
   */
  async processData(deviceId, rawData) {
    let processedData = rawData;
    let eventType = null;
    let wasProcessed = false;

    for (const plugin of this.middlewares) {
      try {
        if (plugin.middleware.canProcess(deviceId, processedData)) {
          const result = await plugin.middleware.process(deviceId, processedData);
          
          if (result !== null && result !== undefined) {
            // Le middleware doit retourner un objet avec eventType et data
            if (typeof result === 'object' && result.hasOwnProperty('eventType') && result.hasOwnProperty('data')) {
              processedData = result.data;
              eventType = result.eventType;
              wasProcessed = true;
            } else {
              console.error(`❌ Invalid middleware response format from ${plugin.name}: expected {eventType, data}`);
            }
            
            // Pour l'instant, on ne traite qu'avec le premier middleware qui correspond
            // On pourrait étendre ça pour permettre le chaînage
            break;
          }
        }
      } catch (error) {
        console.error(`❌ Error in middleware ${plugin.name}:`, error.message);
        // Continuer avec les autres middlewares en cas d'erreur
      }
    }

    return {
      data: processedData,
      eventType: eventType,
      processed: wasProcessed
    };
  }

  /**
   * Obtenir la liste des plugins chargés
   */
  getLoadedPlugins() {
    return Array.from(this.loadedPlugins.keys());
  }

  /**
   * Obtenir un middleware par son nom
   */
  getMiddlewareByName(name) {
    const plugin = this.loadedPlugins.get(name);
    return plugin ? plugin.middleware : null;
  }

  /**
   * Obtenir les statistiques des plugins
   */
  getStats() {
    return {
      totalMiddlewares: this.middlewares.length,
      loadedPlugins: this.getLoadedPlugins()
    };
  }
}

export default PluginManager;