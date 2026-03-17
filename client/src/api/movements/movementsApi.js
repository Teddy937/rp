import http from "@/api/http";
import { CustomAxiosError } from "@/helpers/customErrors";

export default {
  async listMovements(params = {}) {
    try {
      const response = await http().get("movements", { params });
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async getMovement(id) {
    try {
      const response = await http().get(`movements/${id}`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async getPendingTransfers() {
    try {
      const response = await http().get("movements/pending-transfers");
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async recordSale(data) {
    try {
      const response = await http().post("movements/sale", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async recordTransfer(data) {
    try {
      const response = await http().post("movements/transfer", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async approveTransfer(id) {
    try {
      const response = await http().post(`movements/transfer/${id}/approve`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async rejectTransfer(id, reason) {
    try {
      const response = await http().post(`movements/transfer/${id}/reject`, {
        reason,
      });
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async recordAdjustment(data) {
    try {
      const response = await http().post("movements/adjustment", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async recordProcurement(data) {
    try {
      const response = await http().post("movements/procurement", data);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
};
