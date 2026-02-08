import React, { useRef, useEffect } from 'react';

function Starfield() {
  const canvasRef = useRef(null);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let animationId;
    let particles = [];
    const PARTICLE_COUNT = 80;

    function resize() {
      canvas.width = window.innerWidth;
      canvas.height = document.documentElement.scrollHeight;
    }

    function createParticles() {
      particles = [];
      for (let i = 0; i < PARTICLE_COUNT; i++) {
        particles.push({
          x: Math.random() * canvas.width,
          y: Math.random() * canvas.height,
          r: Math.random() * 1.5 + 0.5,
          dx: (Math.random() - 0.5) * 0.3,
          dy: (Math.random() - 0.5) * 0.3,
          opacity: Math.random() * 0.5 + 0.2,
          twinkleSpeed: Math.random() * 0.02 + 0.005,
          twinkleOffset: Math.random() * Math.PI * 2,
        });
      }
    }

    function draw(time) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      for (const p of particles) {
        // Twinkle effect
        const twinkle = Math.sin(time * p.twinkleSpeed + p.twinkleOffset);
        const alpha = p.opacity + twinkle * 0.2;

        // Move
        p.x += p.dx;
        p.y += p.dy;

        // Wrap around
        if (p.x < 0) p.x = canvas.width;
        if (p.x > canvas.width) p.x = 0;
        if (p.y < 0) p.y = canvas.height;
        if (p.y > canvas.height) p.y = 0;

        // Draw dot with soft glow
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(255, 255, 255, ${Math.max(0.05, alpha)})`;
        ctx.fill();

        // Subtle glow
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r * 2.5, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(255, 255, 255, ${Math.max(0.01, alpha * 0.15)})`;
        ctx.fill();
      }

      animationId = requestAnimationFrame(draw);
    }

    resize();
    createParticles();
    animationId = requestAnimationFrame(draw);

    const resizeObserver = new ResizeObserver(() => {
      resize();
    });
    resizeObserver.observe(document.body);

    window.addEventListener('resize', resize);

    return () => {
      cancelAnimationFrame(animationId);
      window.removeEventListener('resize', resize);
      resizeObserver.disconnect();
    };
  }, []);

  return (
    <canvas
      ref={canvasRef}
      className="fixed inset-0 pointer-events-none z-0"
      style={{ background: 'transparent' }}
    />
  );
}

export default Starfield;
