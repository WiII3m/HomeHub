#!/bin/bash

# Script de démarrage pour HomeHub Real-time Server

echo "🏠 HomeHub Real-time Server Starter"
echo "==================================="

# Vérifier que Node.js est installé
if ! command -v node &> /dev/null; then
    echo "❌ Node.js n'est pas installé. Installez-le avec:"
    echo "   brew install node"
    exit 1
fi

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "package.json" ]; then
    echo "❌ Erreur: Exécutez ce script depuis le dossier node-realtime"
    exit 1
fi

# Vérifier le fichier .env
if [ ! -f ".env" ]; then
    echo "⚠️  Fichier .env manquant. Création depuis l'exemple..."
    cp .env.example .env 2>/dev/null || echo "Créez le fichier .env avec vos credentials Tuya"
fi

# Installer les dépendances si nécessaire
if [ ! -d "node_modules" ]; then
    echo "📦 Installation des dépendances..."
    npm install
fi

echo ""
echo "🚀 Démarrage du serveur..."
echo "💡 Appuyez sur Ctrl+C pour arrêter"
echo ""

# Démarrer le serveur
npm start