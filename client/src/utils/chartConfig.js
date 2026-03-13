// utils/chartConfig.js

export const PALETTE = [
  "#4f8ef7",
  "#22d3a5",
  "#f59e42",
  "#e05c7a",
  "#a78bfa",
  "#38bdf8",
  "#fb923c",
  "#34d399",
  "#f472b6",
  "#facc15",
];

export const PALETTE_ALPHA = (hex, a = 0.5) => {
  const r = parseInt(hex.slice(1, 3), 16);
  const g = parseInt(hex.slice(3, 5), 16);
  const b = parseInt(hex.slice(5, 7), 16);
  return `rgba(${r},${g},${b},${a})`;
};

export function makeAreaGradient(ctx, hex, alpha1 = 0.35, alpha2 = 0.0) {
  const gradient = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
  gradient.addColorStop(0, PALETTE_ALPHA(hex, alpha1));
  gradient.addColorStop(1, PALETTE_ALPHA(hex, alpha2));
  return gradient;
}

export const BASE_OPTIONS = {
  responsive: true,
  maintainAspectRatio: false,
  animation: { duration: 700, easing: "easeInOutQuart" },
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: "#1a2236",
      borderColor: "rgba(99,130,201,0.3)",
      borderWidth: 1,
      titleColor: "#e8eef8",
      bodyColor: "#7a91b8",
      padding: 12,
      cornerRadius: 8,
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: "#7a91b8", font: { size: 11 } },
      border: { display: false },
    },
    y: {
      grid: { color: "rgba(99,130,201,0.08)" },
      ticks: { color: "#7a91b8", font: { size: 11 } },
      border: { display: false },
    },
  },
};

export function barDataset(label, data, colorIndex = 0) {
  const color = PALETTE[colorIndex % PALETTE.length];
  return {
    label,
    data,
    backgroundColor: PALETTE_ALPHA(color, 0.7),
    borderColor: color,
    borderWidth: 2,
    borderRadius: 8,
    borderSkipped: false,
    hoverBackgroundColor: PALETTE_ALPHA(color, 0.9),
  };
}

export function lineDataset(ctx, label, data, colorIndex = 0) {
  const color = PALETTE[colorIndex % PALETTE.length];
  return {
    label,
    data,
    borderColor: color,
    backgroundColor: ctx
      ? makeAreaGradient(ctx, color)
      : PALETTE_ALPHA(color, 0.2),
    borderWidth: 2.5,
    tension: 0.4,
    fill: true,
    pointRadius: 3,
    pointHoverRadius: 6,
    pointBackgroundColor: color,
  };
}

export function formatNumber(n) {
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + "M";
  if (n >= 1_000) return (n / 1_000).toFixed(1) + "K";
  return String(n ?? 0);
}

export function formatPct(n) {
  const v = parseFloat(n ?? 0);
  return (v >= 0 ? "+" : "") + v.toFixed(1) + "%";
}
