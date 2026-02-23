tailwind.config = {
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
            colors: {
                slate: {
                    850: '#151e2e',
                }
            },
            animation: {
                'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                'blob': 'blob 7s infinite',
            },
            keyframes: {
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                blob: {
                    '0%': { transform: 'translate(0px, 0px) scale(1)' },
                    '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                    '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                    '100%': { transform: 'translate(0px, 0px) scale(1)' },
                }
            }
        }
    }
}

// Lógica del carrusel de imágenes
document.addEventListener('DOMContentLoaded', () => {
    const carousel = document.getElementById('image-carousel');
    if (carousel) {
        let autoScrollInterval;

        const startAutoScroll = () => {
            autoScrollInterval = setInterval(() => {
                const maxScrollLeft = carousel.scrollWidth - carousel.clientWidth;
                // Da un margen de 10px por posibles decimales en el cálculo de scroll
                if (carousel.scrollLeft >= maxScrollLeft - 10) {
                    // Si llega al final, vuelve al inicio
                    carousel.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    // Si no, avanza el tamaño del contenedor
                    carousel.scrollBy({ left: carousel.clientWidth, behavior: 'smooth' });
                }
            }, 3000); // Cambia de imagen cada 3 segundos
        };

        const stopAutoScroll = () => {
            clearInterval(autoScrollInterval);
        };

        // Iniciar auto-scroll
        startAutoScroll();

        // Pausar auto-scroll al interactuar
        carousel.addEventListener('mouseenter', stopAutoScroll);
        carousel.addEventListener('mouseleave', startAutoScroll);
        carousel.addEventListener('touchstart', stopAutoScroll, { passive: true });
        carousel.addEventListener('touchend', startAutoScroll, { passive: true });
    }
});