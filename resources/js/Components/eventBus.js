// Simple event bus without external deps
const listeners = new Map();

function on(event, cb) {
  if (!listeners.has(event)) listeners.set(event, new Set());
  listeners.get(event).add(cb);
}

function off(event, cb) {
  const set = listeners.get(event);
  if (!set) return;
  set.delete(cb);
}

function emit(event, payload) {
  const set = listeners.get(event);
  if (!set) return;
  for (const cb of Array.from(set)) {
    try { cb(payload); } catch {}
  }
}

export default { on, off, emit };
