const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');

// Load SSL certificate and key
const server = https.createServer({
  cert: fs.readFileSync('/home/ezemscok/ssl/cert/healthtech_ezems_co_ke_baa55_5242b_1731176486_c36e87c648e3e21884f0ee55cf4dccf0.crt'),
  key: fs.readFileSync('/home/ezemscok/ssl/key/baa55_5242b_bf8e9894422095c536bee80d3142b0db.key')
});

// Error handling for the server
server.on('error', (err) => {
  console.error('Server error:', err);
});

// Create a WebSocket server on the HTTPS server
const wss = new WebSocket.Server({ server });

// Error handling for the WebSocket server
wss.on('error', (err) => {
  console.error('WebSocket server error:', err);
});

// Store connected clients and their associated room IDs
const clients = new Map();

// Handle new client connections
wss.on('connection', (ws) => {
  console.log('New client connected');

  // Handle incoming messages from clients
  ws.on('message', (message) => {
    let data;
    try {
      data = JSON.parse(message);
    } catch (err) {
      console.error('Invalid message format:', message);
      return;
    }

    switch (data.type) {
      case 'join':
        // Store client and room ID in the map
        clients.set(ws, data.room_id);
        break;
      case 'message':
        // Broadcast message to all clients in the same room
        broadcast(data, ws);
        break;
      case 'delete':
        // Broadcast delete message to all clients in the same room
        broadcastDelete(data, ws);
        break;
      case 'audio':
        // Broadcast audio message to all clients in the same room
        broadcastAudio(data, ws);
        break;
      default:
        console.error('Unknown message type:', data.type);
    }
  });

  // Handle client disconnections
  ws.on('close', () => {
    console.log('Client disconnected');
    // Remove client from the map
    clients.delete(ws);
  });
});

// Broadcast message to all clients in the same room except the sender
function broadcast(data, sender) {
  clients.forEach((roomId, client) => {
    if (client !== sender && roomId === data.room_id) {
      client.send(JSON.stringify(data));
    }
  });
}

// Broadcast delete message to all clients in the same room
function broadcastDelete(data, sender) {
  clients.forEach((roomId, client) => {
    if (roomId === data.room_id) {
      client.send(JSON.stringify(data));
    }
  });
}

// Broadcast audio message to all clients in the same room
function broadcastAudio(data, sender) {
  clients.forEach((roomId, client) => {
    if (roomId === data.room_id) {
      client.send(JSON.stringify(data));
    }
  });
}

// Start the server on port 443
server.listen(443, () => {
  console.log('WebSocket server is running on wss://healthtech.ezems.co.ke:443');
});
