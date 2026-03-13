import http from "@/api/http";
import { CustomAxiosError } from "@/helpers/customErrors";

export default {
  async listUsers(params = {}) {
    try {
      const response = await http().get("users", { params });
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
  async getRoles() {
    try {
      const response = await http().get("users/roles");
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
  async getUser(id) {
    try {
      const response = await http().get(`users/${id}`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
  async createUser(data) {
    try {
      const response = await http().post("users", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
  async updateUser(id, data) {
    try {
      const response = await http().put(`users/${id}`, data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
  async resetPassword(id, data) {
    try {
      const response = await http().post(`users/${id}/reset-password`, data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
  async toggleStatus(id, account_status) {
    try {
      const response = await http().post(`users/${id}/status`, {
        account_status,
      });
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
  async deleteUser(id) {
    try {
      const response = await http().delete(`users/${id}`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
  async listAuditLogs(params = {}) {
    try {
      const response = await http().get("audit-logs", { params });
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
};
