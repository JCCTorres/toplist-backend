import React, { useRef, useEffect } from 'react';

function Starfield() {
  const canvasRef = useRef(null);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let animationId;
    let particles = [];
    const PARTICLE_COUNT = 200;

    function resize() {
      canvas.width = window.innerWidth;
      canvas.height = document.documentElement.scrollHeight;
    }

    function createParticles() {
      particles = [];
      for (let i = 0; i < PARTICLE_COUNT; i++) {
        // Mix of warm white, gold, and faint blue tones for a summery feel
        const colorRoll = Math.random();
        let r, g, b;
        if (colorRoll < 0.5) {
          // Warm white / cream
          r = 255; g = 250; b = 240;
        } else if (colorRoll < 0.8) {
          // Soft gold shimmer
          r = 255; g = 215; b = 140;
        } else {
          // Faint sky blue
          r = 180; g = 220; b = 255;
        }

        particles.push({
          x: Math.random() * canvas.width,
          y: Math.random() * canvas.height,
          r: Math.random() * 0.8 + 0.2, // smaller: 0.2 to 1.0
          dx: (Math.random() - 0.5) * 0.15,
          dy: (Math.random() - 0.5) * 0.15,
          opacity: Math.random() * 0.3 + 0.08,
          twinkleSpeed: Math.random() * 0.015 + 0.003,
          twinkleOffset: Math.random() * Math.PI * 2,
          color: { r, g, b },
        });
      }
    }

    function draw(time) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      for (const p of particles) {
        const twinkle = Math.sin(time * p.twinkleSpeed + p.twinkleOffset);
        const alpha = p.opacity + twinkle * 0.12;

        p.x += p.dx;
        p.y += p.dy;

        if (p.x < 0) p.x = canvas.width;
        if (p.x > canvas.width) p.x = 0;
        if (p.y < 0) p.y = canvas.height;
        if (p.y > canvas.height) p.y = 0;

        // Tiny dot
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(${p.color.r}, ${p.color.g}, ${p.color.b}, ${Math.max(0.03, alpha)})`;
        ctx.fill();

        // Minimal glow — only for larger particles
        if (p.r > 0.5) {
          ctx.beginPath();
          ctx.arc(p.x, p.y, p.r * 2, 0, Math.PI * 2);
          ctx.fillStyle = `rgba(${p.color.r}, ${p.color.g}, ${p.color.b}, ${Math.max(0.01, alpha * 0.08)})`;
          ctx.fill();
        }
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
