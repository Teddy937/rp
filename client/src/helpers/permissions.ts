// utils/permissions.ts
import { getSession } from "@/api/http";

export const getUserPermissions = (): string[] => {
  try {
    return getSession().permissions;
  } catch (e) {
    return [];
  }
};

export const getSystemModules = () => {
  try {
    return getSession().modules;
  } catch (e) {
    return [];
  }
};

export const hasPermission = (permissionName: string): boolean => {
  const permissions = getUserPermissions();
  return permissions.includes(permissionName);
};

/**
 * @param {Array} moduleNames - Module names to check
 *
 * @returns {Boolean} - True if user has at least one permission in any of the given modules
 */
export const hasPermissionInModules = (moduleNames: string[]): boolean => {
  const allModules = getSystemModules();
  const userPermissions = getUserPermissions();
  return allModules.some((module: { name: any; permissions: any[] }) => {
    if (!moduleNames.includes(module.name)) return false;

    return module.permissions.some((permission) =>
      userPermissions.includes(permission)
    );
  });
};
