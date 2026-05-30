const express = require('express');
const http = require('http');
const cors = require('cors');
const { Server } = require('socket.io');

const PORT = Number(process.env.PORT) || 3001;

const app = express();
app.use(cors({ origin: '*' }));
app.use(express.json());

const httpServer = http.createServer(app);

const io = new Server(httpServer, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST'],
  },
  pingInterval: 10000,
  pingTimeout: 5000,
});

const userRoom = username => `user:${username}`;

const buildPayload = (title, message, type = 'info', extra = {}) => ({
  id: `${Date.now()}-${Math.random().toString(36).slice(2, 9)}`,
  title,
  message,
  type,
  createdAt: new Date().toISOString(),
  ...extra,
});

io.on('connection', socket => {
  console.log(`[Webdev_Project2] socket connected ${socket.id}`);

  socket.on('join', ({ username }) => {
    if (!username) {
      return;
    }

    socket.data.username = username;
    socket.join(userRoom(username));
    console.log(`[Webdev_Project2] ${username} joined ${userRoom(username)}`);
  });

  socket.on('notify', payload => {
    const { username, title, message, type, broadcast } = payload || {};

    if (!title || !message) {
      return;
    }

    const notification = buildPayload(title, message, type);

    if (broadcast) {
      io.emit('notification', notification);
      return;
    }

    if (username) {
      io.to(userRoom(username)).emit('notification', notification);
    }
  });

  socket.on('disconnect', () => {
    console.log(`[Webdev_Project2] socket disconnected ${socket.id}`);
  });
});

app.get('/health', (_req, res) => {
  res.json({ ok: true, service: 'webdev-project2-socket', project: 'Webdev_Project2' });
});

app.post('/api/notify', (req, res) => {
  const { username, title, message, type, broadcast } = req.body || {};

  if (!title || !message) {
    return res.status(400).json({ success: false, message: 'title and message required' });
  }

  const notification = buildPayload(title, message, type || 'info');

  if (broadcast) {
    io.emit('notification', notification);
  } else if (username) {
    io.to(userRoom(username)).emit('notification', notification);
  } else {
    return res.status(400).json({
      success: false,
      message: 'username or broadcast required',
    });
  }

  return res.json({ success: true, notification });
});

httpServer.listen(PORT, '0.0.0.0', () => {
  console.log(`Webdev_Project2 Socket.io server listening on port ${PORT}`);
});
