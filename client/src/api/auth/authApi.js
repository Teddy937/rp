import http from "../http";
import { CustomAxiosError } from "@/helpers/customErrors";

export default {
  /**
   * STEP 1 — Submit email + password.
   * Returns { login_token, otp (dev only) } on success.
   * Frontend stores login_token temporarily for step 2.
   */
  async loginApi(data) {
    try {
      const response = await http().post("auth/login", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  /**
   * STEP 2 — Submit login_token + OTP.
   * Returns { token, user } on success — store token in auth store.
   */
  async verifyOtpApi(data) {
    try {
      const response = await http().post("auth/verify-otp", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  /**
   * Resend OTP — pass the same login_token from step 1.
   */
  async resendOtpApi(data) {
    try {
      const response = await http().post("auth/resend-otp", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  /**
   * Forgot password — sends reset token to user's email.
   */
  async forgotPasswordApi(data) {
    try {
      const response = await http().post("auth/forgot-password", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  /**
   * Reset password using token from email.
   */
  async resetPasswordApi(data) {
    try {
      const response = await http().post("auth/reset-password", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  /**
   * Change password (authenticated user).
   */
  async changePasswordApi(data) {
    try {
      const response = await http().post("auth/change-password", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  /**
   * Get current authenticated user.
   */
  async meApi() {
    try {
      const response = await http().get("auth/me");
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  /**
   * Logout — revokes Sanctum token.
   */
  async logoutApi() {
    try {
      const response = await http().post("auth/logout");
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  /**
   * Heartbeat — keep session alive, refresh cached user data.
   */
  async heartbeatApi() {
    try {
      const response = await http().get("heartbeat");
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
};
