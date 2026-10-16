<template>
    <div
        class="relative h-full w-full rounded-[44px] bg-[#1a0a02] p-[10px] shadow-[0_40px_80px_-30px_rgba(33,13,2,0.6),inset_0_0_0_1.5px_rgba(255,255,255,0.12)]"
    >
        <div
            class="relative h-full w-full overflow-hidden rounded-[35px] bg-white"
        >
            <div
                aria-hidden="true"
                class="absolute top-2.5 left-1/2 z-30 h-[22px] w-[86px] -translate-x-1/2 rounded-full bg-[#1a0a02]"
            />
            <slot />
        </div>
    </div>
</template>
