import CryptoJS from "crypto-js";
import _ from "lodash";
import { getSession } from "@/api/http";

export const formatDate = (dateStr: string, type = false) => {
  const date = new Date(dateStr);
  if (type) {
    return date.toLocaleString("en-GB", {
      weekday: "short", // 'Mon', 'Tue', etc.
      year: "numeric", // '2025'
      month: "short", // 'Mar'
      day: "numeric", // '9'
      hour: "2-digit", // '23'
      minute: "2-digit", // '17'
      second: "2-digit", // '21'
    });
  } else {
    return date.toLocaleString("en-GB", {
      weekday: "short", // 'Mon', 'Tue', etc.
      year: "numeric", // '2025'
      month: "short", // 'Mar'
      day: "numeric", // '9'
      // hour: "2-digit", // '23'
      // minute: "2-digit", // '17'
      // second: "2-digit", // '21'
    });
  }
};

export const roundTo = (num: any, decimal: number) => {
  const factor = Math.pow(10, decimal);
  return Math.round(num * factor) / factor;
};

export const getUser = () => {
  return getSession().user;
};

export const isImage = (file: { url: string }) => {
  const ext = file.url.split(".").pop()?.toLowerCase().split("?")[0];
  return ["jpg", "jpeg", "png", "gif", "bmp", "webp"].includes(ext || "");
};

export const formatMoney = (amount: any) => {
  if (isNaN(amount)) return "0.00"; // handle invalid input
  return Number(amount).toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};

export const resetForm = (obj: any) => {
  for (const key in obj) {
    const value = obj[key];

    if (value instanceof File) {
      obj[key] = null; // or '' depending on your design
      continue;
    }

    if (Array.isArray(value)) {
      if (
        value.length > 0 &&
        typeof value[0] === "object" &&
        value[0] !== null
      ) {
        obj[key] = value.map((item: any) => {
          const newItem: any = {};
          for (const subKey in item) {
            const subValue = item[subKey];
            newItem[subKey] =
              typeof subValue === "number"
                ? null
                : typeof subValue === "object" && subValue !== null
                  ? resetForm({ ...subValue })
                  : "";
          }
          return newItem;
        });
      } else {
        obj[key] = [];
      }
    } else if (value !== null && typeof value === "object") {
      resetForm(value);
    } else if (typeof value === "number") {
      obj[key] = null;
    } else {
      obj[key] = "";
    }
  }

  return obj;
};

export const getInitials = (name: string) => {
  if (!name) return "";

  const parts = name.trim().split(/\s+/);

  if (parts.length === 1) {
    return parts[0][0].toUpperCase();
  }

  const first = parts[0][0].toUpperCase();
  const last = parts[parts.length - 1][0].toUpperCase();

  return first + last;
};

export const formatNumber = (num: number) => {
  if (typeof num !== "number") {
    num = parseFloat(num);
  }

  if (isNaN(num)) return "";

  return num.toLocaleString("en-US", {
    maximumFractionDigits: 2,
  });
};

export const capitalizeFirstLetter = (word: string): string => {
  if (!word) return "";

  return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
};

export const formatLabel = (str: string) => {
  return _.startCase(_.toLower(str));
};

export const getDeviceInfo = () => {
  const nav = navigator as any; // bypass TS type checking for non-standard properties

  return {
    user_agent: nav.userAgent,
    platform: nav.platform,
    productSub: nav.productSub,
    product: nav.product,
    language: nav.language,
    screen: `${window.screen.width}x${window.screen.height}`,
    color_depth: window.screen.colorDepth,
    pixel_ratio: window.devicePixelRatio || 1,
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    hardware_concurrency: nav.hardwareConcurrency || null,
    device_memory: nav.deviceMemory || null,
  };
};
