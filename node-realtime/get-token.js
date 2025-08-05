#!/usr/bin/env node

import axios from 'axios';
import dotenv from 'dotenv';
dotenv.config();

const laravelUrl = process.env.APP_URL || 'http://localhost:8000';

console.log('🔑 Getting JWT token from Laravel...');
console.log(`📍 Laravel URL: ${laravelUrl}`);

try {
  // Simuler une authentification pour obtenir un token
  // (en supposant qu'il y a une route pour ça)
  const response = await axios.post(`${laravelUrl}/api/websocket/validate-token`, {
    token: 'test'
  });
  
  console.log('Response:', response.data);
} catch (error) {
  console.log('❌ Error:', error.message);
  if (error.response) {
    console.log('Response data:', error.response.data);
  }
}