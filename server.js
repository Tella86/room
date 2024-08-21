const fs = require('fs');
const https = require('https');
const WebSocket = require('ws');

// Load SSL certificate and key
let server;
try {
  server = https.createServer({
    cert: fs.readFileSync('/home/ezemscok/ssl/certs/healthtech_ezems_co_ke_9ccea_f5941_1731881759_873ed662eedf476d3dcac24949c4fd20'),
    key: fs.readFileSync('/home/ezemscok/ssl/keys/9ccea_f5941_b5a97e6601a20c9fa3328cbf48e1f39b.key')
  });
} catch (err) {
  console.error('Failed to load SSL files:', err);
  process.exit(1);  // Exit if SSL files cannot be loaded
}

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
    console.log('Received:', message);  // Print the received message

    let data;
    try {
      data = JSON.parse(message);
      console.log('Parsed message data:', data);  // Print the parsed data
    } catch (err) {
      console.error('Invalid message format:', message);
      return;
    }

    switch (data.type) {
      case 'join':
        // Store client and room ID in the map
        clients.set(ws, data.room_id);
        console.log(`Client joined room: ${data.room_id}`);
        break;
      case 'message':
        console.log(`Broadcasting message in room ${data.room_id}: ${data.content}`);
        // Broadcast message to all clients in the same room
        broadcast(data, ws);
        break;
      case 'delete':
        console.log(`Broadcasting delete in room ${data.room_id}`);
        // Broadcast delete message to all clients in the same room
        broadcastDelete(data, ws);
        break;
      case 'audio':
        console.log(`Broadcasting audio in room ${data.room_id}`);
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
      console.log('Sent message to client:', data);
    }
  });
}

// Broadcast delete message to all clients in the same room
function broadcastDelete(data, sender) {
  clients.forEach((roomId, client) => {
    if (roomId === data.room_id) {
      client.send(JSON.stringify(data));
      console.log('Sent delete message to client:', data);
    }
  });
}

// Broadcast audio message to all clients in the same room
function broadcastAudio(data, sender) {
  clients.forEach((roomId, client) => {
    if (roomId === data.room_id) {
      client.send(JSON.stringify(data));
      console.log('Sent audio message to client:', data);
    }
  });
}

// Graceful shutdown
function shutdown() {
  console.log('Shutting down server...');
  wss.clients.forEach((client) => client.close());
  server.close(() => {
    console.log('Server closed');
    process.exit(0);
  });
}

process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);

// Start the server on port 443
server.listen(443, () => {
  console.log('WebSocket server is running on wss://healthtech.ezems.co.ke:443');
});
