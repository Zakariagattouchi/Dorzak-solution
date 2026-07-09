let audioContext: AudioContext | null = null;

const ensureContext = (): AudioContext | null => {
  if (typeof window === 'undefined') return null;
  if (!audioContext) {
    const Ctx = (window.AudioContext || (window as any).webkitAudioContext);
    if (Ctx) audioContext = new Ctx();
  }
  return audioContext;
};

export const playOrderNotificationSound = (): void => {
  const ctx = ensureContext();
  if (!ctx) return;

  // Resume context in case it was suspended by the browser autoplay policy.
  if (ctx.state === 'suspended') {
    ctx.resume().catch(() => {});
  }

  const now = ctx.currentTime;

  // Two-tone chime: D5 -> A5, pleasant and attention-grabbing.
  const frequencies = [587.33, 880];
  frequencies.forEach((freq, index) => {
    const oscillator = ctx.createOscillator();
    const gain = ctx.createGain();

    oscillator.type = 'sine';
    oscillator.frequency.setValueAtTime(freq, now + index * 0.18);

    const start = now + index * 0.18;
    const attack = 0.02;
    const decay = 0.35;
    gain.gain.setValueAtTime(0, start);
    gain.gain.linearRampToValueAtTime(0.25, start + attack);
    gain.gain.exponentialRampToValueAtTime(0.001, start + attack + decay);

    oscillator.connect(gain);
    gain.connect(ctx.destination);

    oscillator.start(start);
    oscillator.stop(start + attack + decay + 0.05);
  });
};

export const requestNotificationPermission = async (): Promise<NotificationPermission | null> => {
  if (typeof window === 'undefined' || !('Notification' in window)) return null;
  if (Notification.permission === 'granted') return 'granted';
  try {
    return await Notification.requestPermission();
  } catch {
    return Notification.permission;
  }
};

export const showOrderNotification = (title: string, body: string): void => {
  if (typeof window === 'undefined' || !('Notification' in window)) return;
  if (Notification.permission === 'granted') {
    new Notification(title, { body, icon: '/favicon.svg' });
  }
};
