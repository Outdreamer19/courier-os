<script setup lang="ts">
import { ref } from 'vue';

interface Testimonial {
    quote: string;
    name: string;
    company: string;
}

const props = withDefaults(defineProps<{ testimonials?: Testimonial[] }>(), { testimonials: () => [] });
const active = ref(0);
</script>

<template>
    <section v-if="props.testimonials.length" class="bg-white py-20 fade-in-section">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <p class="text-2xl font-medium leading-relaxed tracking-tight text-marketing-ink sm:text-3xl">
                "{{ props.testimonials[active].quote }}"
            </p>
            <p class="mt-4 text-sm font-semibold text-marketing-ink-muted">
                {{ props.testimonials[active].name }} · {{ props.testimonials[active].company }}
            </p>
            <div v-if="props.testimonials.length > 1" class="mt-6 flex justify-center gap-2">
                <button
                    v-for="(t, index) in props.testimonials"
                    :key="t.company"
                    type="button"
                    class="size-2 rounded-full transition"
                    :class="active === index ? 'bg-marketing-orange' : 'bg-marketing-eggshell'"
                    :aria-label="`Show testimonial from ${t.company}`"
                    @click="active = index"
                />
            </div>
        </div>
    </section>
</template>
