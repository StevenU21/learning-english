<template>
  <div class="relative w-full max-w-xs">
    <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
      <div class="h-full transition-all duration-300" :style="{
        width: pct + '%',
        backgroundColor: barColor
      }"></div>
    </div>
    <div class="absolute inset-0 flex items-center justify-center">
      <span class="text-[10px] sm:text-xs font-semibold text-gray-900 dark:text-gray-100">
        {{ pct }}%
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  value: { type: [Number, String], required: true }
});

const pct = computed(() => {
  const num = Number(props.value) || 0;
  return Math.min(Math.max(Math.round(num), 0), 100);
});

// Interpolación de color: 0% rojo (#ef4444), 50% amarillo (#facc15), 100% verde (#22c55e)
function interpolateColor(color1, color2, factor) {
  const c1 = color1.match(/\w\w/g).map(x => parseInt(x, 16));
  const c2 = color2.match(/\w\w/g).map(x => parseInt(x, 16));
  return (
    '#' +
    c1.map((c, i) => Math.round(c + (c2[i] - c) * factor).toString(16).padStart(2, '0')).join('')
  );
}

const barColor = computed(() => {
  // 0-50: rojo a amarillo, 50-100: amarillo a verde
  if (pct.value <= 50) {
    const f = pct.value / 50;
    return interpolateColor('ef4444', 'facc15', f); // rojo a amarillo
  } else {
    const f = (pct.value - 50) / 50;
    return interpolateColor('facc15', '22c55e', f); // amarillo a verde
  }
});
</script>
