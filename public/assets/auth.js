(() => {
    'use strict';

    const canvas = document.querySelector('.auth-waves');
    if (canvas instanceof HTMLCanvasElement) {
        const context = canvas.getContext('2d');
        if (context) {
            class Noise2D {
                constructor(seed) {
                    this.permutation = Array.from({ length: 256 }, (_, index) => index);
                    let state = seed >>> 0;
                    const random = () => {
                        state = (state * 1664525 + 1013904223) >>> 0;
                        return state / 4294967296;
                    };

                    for (let index = this.permutation.length - 1; index > 0; index -= 1) {
                        const swapIndex = Math.floor(random() * (index + 1));
                        [this.permutation[index], this.permutation[swapIndex]] = [
                            this.permutation[swapIndex],
                            this.permutation[index],
                        ];
                    }

                    this.permutation = [...this.permutation, ...this.permutation];
                }

                noise(x, y) {
                    const cellX = Math.floor(x);
                    const cellY = Math.floor(y);
                    const offsetX = x - cellX;
                    const offsetY = y - cellY;
                    const indexX = cellX & 255;
                    const indexY = cellY & 255;
                    const fadeX = offsetX * offsetX * offsetX * (offsetX * (offsetX * 6 - 15) + 10);
                    const fadeY = offsetY * offsetY * offsetY * (offsetY * (offsetY * 6 - 15) + 10);
                    const gradient = (hash, dx, dy) => {
                        const direction = hash & 7;
                        const horizontal = direction < 4 ? dx : dy;
                        const vertical = direction < 4 ? dy : dx;
                        return ((direction & 1) === 0 ? horizontal : -horizontal)
                            + ((direction & 2) === 0 ? vertical : -vertical);
                    };
                    const topLeft = gradient(this.permutation[indexX + this.permutation[indexY]], offsetX, offsetY);
                    const topRight = gradient(this.permutation[indexX + 1 + this.permutation[indexY]], offsetX - 1, offsetY);
                    const bottomLeft = gradient(this.permutation[indexX + this.permutation[indexY + 1]], offsetX, offsetY - 1);
                    const bottomRight = gradient(this.permutation[indexX + 1 + this.permutation[indexY + 1]], offsetX - 1, offsetY - 1);
                    const top = topLeft + (topRight - topLeft) * fadeX;
                    const bottom = bottomLeft + (bottomRight - bottomLeft) * fadeX;

                    return (top + (bottom - top) * fadeY) * 0.5;
                }
            }

            const noise = new Noise2D(318517);
            const xGap = 12;
            const yGap = 24;
            const friction = 0.92;
            const tension = 0.004;
            const maxCursorMove = 80;
            const pointer = {
                x: -10,
                y: 0,
                lastX: 0,
                lastY: 0,
                smoothX: 0,
                smoothY: 0,
                speed: 0,
                smoothSpeed: 0,
                angle: 0,
                initialized: false,
            };
            let width = 0;
            let height = 0;
            let lines = [];
            let animationFrame = 0;
            let lastFrame = 0;
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

            const resize = () => {
                const pixelRatio = Math.min(window.devicePixelRatio || 1, 1.25);
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = Math.round(width * pixelRatio);
                canvas.height = Math.round(height * pixelRatio);
                context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

                const totalColumns = Math.ceil((width + 200) / xGap);
                const totalRows = Math.ceil((height + 30) / yGap);
                const startX = (width - xGap * totalColumns) / 2;
                const startY = (height - yGap * totalRows) / 2;

                lines = Array.from({ length: totalColumns + 1 }, (_, column) => (
                    Array.from({ length: totalRows + 1 }, (_, row) => ({
                        x: startX + xGap * column,
                        y: startY + yGap * row,
                        cursorX: 0,
                        cursorY: 0,
                        velocityX: 0,
                        velocityY: 0,
                    }))
                ));
            };

            const movePoints = (timestamp) => {
                lines.forEach((line) => {
                    line.forEach((point) => {
                        const flow = noise.noise(
                            (point.x + timestamp * 0.008) * 0.002,
                            (point.y + timestamp * 0.003) * 0.0015
                        ) * 12;
                        point.waveX = Math.cos(flow) * 24;
                        point.waveY = Math.sin(flow) * 12;

                        if (pointer.initialized) {
                            const dx = point.x - pointer.smoothX;
                            const dy = point.y - pointer.smoothY;
                            const distance = Math.hypot(dx, dy);
                            const radius = Math.max(150, pointer.smoothSpeed);
                            if (distance < radius) {
                                const strength = 1 - distance / radius;
                                const force = Math.cos(distance * 0.001) * strength * radius * pointer.smoothSpeed * 0.0004;
                                point.velocityX += Math.cos(pointer.angle) * force;
                                point.velocityY += Math.sin(pointer.angle) * force;
                            }
                        }

                        point.velocityX += -point.cursorX * tension;
                        point.velocityY += -point.cursorY * tension;
                        point.velocityX *= friction;
                        point.velocityY *= friction;
                        point.cursorX = Math.max(-maxCursorMove, Math.min(maxCursorMove, point.cursorX + point.velocityX * 2));
                        point.cursorY = Math.max(-maxCursorMove, Math.min(maxCursorMove, point.cursorY + point.velocityY * 2));
                    });
                });
            };

            const draw = (timestamp) => {
                context.clearRect(0, 0, width, height);

                lines.forEach((line) => {
                    context.beginPath();
                    line.forEach((point, index) => {
                        const x = point.x + point.waveX + point.cursorX;
                        const y = point.y + point.waveY + point.cursorY;
                        if (index === 0) {
                            context.moveTo(x, y);
                        } else {
                            context.lineTo(x, y);
                        }
                    });
                    context.strokeStyle = 'rgba(157, 190, 230, 0.2)';
                    context.lineWidth = 1;
                    context.stroke();
                });
            };

            const tick = (timestamp) => {
                animationFrame = 0;
                if (!reducedMotion.matches && timestamp - lastFrame < 32) {
                    animationFrame = window.requestAnimationFrame(tick);
                    return;
                }

                lastFrame = timestamp;
                pointer.smoothX += (pointer.x - pointer.smoothX) * 0.1;
                pointer.smoothY += (pointer.y - pointer.smoothY) * 0.1;
                const deltaX = pointer.x - pointer.lastX;
                const deltaY = pointer.y - pointer.lastY;
                pointer.speed = Math.hypot(deltaX, deltaY);
                pointer.smoothSpeed += (pointer.speed - pointer.smoothSpeed) * 0.1;
                pointer.smoothSpeed = Math.min(80, pointer.smoothSpeed);
                pointer.lastX = pointer.x;
                pointer.lastY = pointer.y;
                pointer.angle = Math.atan2(deltaY, deltaX);

                movePoints(timestamp);
                draw(timestamp);

                if (!reducedMotion.matches && !document.hidden) {
                    animationFrame = window.requestAnimationFrame(tick);
                }
            };

            const start = () => {
                if (animationFrame === 0) {
                    animationFrame = window.requestAnimationFrame(tick);
                }
            };

            const stop = () => {
                if (animationFrame !== 0) {
                    window.cancelAnimationFrame(animationFrame);
                    animationFrame = 0;
                }
            };

            resize();
            if (reducedMotion.matches) {
                movePoints(0);
                draw(0);
            } else {
                start();
            }

            window.addEventListener('resize', () => {
                resize();
                if (reducedMotion.matches) {
                    movePoints(performance.now());
                    draw(performance.now());
                }
            }, { passive: true });
            window.addEventListener('pointermove', (event) => {
                pointer.x = event.clientX;
                pointer.y = event.clientY;
                if (!pointer.initialized) {
                    pointer.smoothX = pointer.x;
                    pointer.smoothY = pointer.y;
                    pointer.lastX = pointer.x;
                    pointer.lastY = pointer.y;
                    pointer.initialized = true;
                }
            }, { passive: true });
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stop();
                } else {
                    start();
                }
            });
            reducedMotion.addEventListener('change', () => {
                if (reducedMotion.matches) {
                    stop();
                    movePoints(performance.now());
                    draw(performance.now());
                } else if (!document.hidden) {
                    start();
                }
            });
        }
    }

    document.addEventListener('click', (event) => {
        const button = event.target instanceof Element
            ? event.target.closest('[data-password-toggle]')
            : null;
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        const input = document.getElementById(button.dataset.passwordToggle || '');
        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(showPassword));
        button.setAttribute('aria-label', showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
    });
})();
