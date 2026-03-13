import { createRouter, createWebHistory } from "vue-router";
import { allRoute } from "@/router/routes";
import { hasPermission } from "@/helpers/permissions";
import http, { clearSession, getSession, setSession } from "@/api/http";
import NProgress from "nprogress";
import "nprogress/nprogress.css";

NProgress.configure({ showSpinner: false });

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: allRoute,
  // scrollBehavior(to, from, savedPosition) {
  //   // Use saved position on browser back/forward
  //   if (savedPosition) {
  //     return savedPosition;
  //   }

  //   // If there's a hash, scroll to anchor
  //   if (to.hash) {
  //     return {
  //       el: to.hash,
  //       behavior: 'smooth',
  //     };
  //   }

  //   // Default: scroll to top on new navigation
  //   return { top: 0 };
  // },
});

router.beforeEach(async (routeTo, routeFrom, next) => {
  NProgress.start();
  const previousPath = routeTo.fullPath;
  const title = routeTo.meta.title;
  if (title) {
    document.title = title.toString();
  }

  const authRequired = routeTo.matched.some((route) => route.meta.authRequired);

  if (authRequired && !getSession().user) {
    try {
      const response = await http().get("heartbeat");
      setSession(response?.data ?? {});
    } catch (err) {
      return redirectToLogin(previousPath);
    }
  }

  const requiredPermission = routeTo.meta.permission as unknown as string;
  if (requiredPermission && !hasPermission(requiredPermission)) {
    return next({ name: "error.403" });
  }

  return next();
});

router.afterEach(() => {
  NProgress.done();
});

const redirectToLogin = async (previousPath: string) => {
  clearSession();
  NProgress.done();
  // Pass the original route to the login component
  router.push({
    name: "auth.sign-in",
    query: { redirectedFrom: previousPath },
  });
};
export default router;
