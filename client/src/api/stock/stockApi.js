import http from "@/api/http";
import { CustomAxiosError } from "@/helpers/customErrors";

export default {
  async getStoreStock(storeId, params = {}) {
    try {
      const response = await http().get(`stock/stores/${storeId}/ledger`, {
        params,
      });
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async getSkuStock(storeId, skuId) {
    try {
      const response = await http().get(
        `stock/stores/${storeId}/skus/${skuId}`,
      );
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },

  async getLowStock(storeId) {
    try {
      const response = await http().get(`stock/stores/${storeId}/low-stock`);
      return response.data;
    } catch (error) {
      throw new CustomAxiosError(error.response?.data || error.message);
    }
  },
};
