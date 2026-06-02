import { onMounted } from 'vue';

type DelayStyle = {
    '--fade-delay': string;
};

export function useScrollReveal() {
    onMounted(() => {
        const targets = Array.from(
            document.querySelectorAll<HTMLElement>('.fade-in-section, .fade-in-item'),
        );

        if (!targets.length) {
            return;
        }

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            },
            {
                root: null,
                threshold: 0.14,
                rootMargin: '0px 0px -8% 0px',
            },
        );

        targets.forEach((el) => observer.observe(el));

        return () => observer.disconnect();
    });

    const sectionDelay = (index: number): DelayStyle => ({
        '--fade-delay': `${index * 120}ms`,
    });

    const itemDelay = (index: number, base = 80): DelayStyle => ({
        '--fade-delay': `${base + index * 70}ms`,
    });

    return { sectionDelay, itemDelay };
}
