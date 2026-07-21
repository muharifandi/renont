package com.nusatim.sapiriku.api.service

import com.nusatim.sapiriku.api.model.ApiEnvelope
import com.nusatim.sapiriku.api.model.ApplicationStatusData
import com.nusatim.sapiriku.api.model.AvailabilityData
import com.nusatim.sapiriku.api.model.CheckAgentData
import com.nusatim.sapiriku.api.model.RegenciesData
import retrofit2.Response
import retrofit2.http.GET
import retrofit2.http.Query

/** Maps to RentonBachkEnd-main/application/modules/api/controllers/Basic.php -- public, no `key` header required. */
interface BasicService {

    @GET("basic/application_status")
    suspend fun applicationStatus(): Response<ApiEnvelope<ApplicationStatusData>>

    /** @param email full email to check */
    @GET("basic/check_email")
    suspend fun checkEmail(@Query("email") email: String): Response<ApiEnvelope<AvailabilityData>>

    /** @param phone full phone number to check */
    @GET("basic/check_phone")
    suspend fun checkPhone(@Query("phone") phone: String): Response<ApiEnvelope<AvailabilityData>>

    /** @param id agent account id */
    @GET("basic/check_agent")
    suspend fun checkAgent(@Query("id") id: String): Response<ApiEnvelope<CheckAgentData>>

    /**
     * Despite the query param name, this is actually a LIKE '%name%' search on the regency
     * name (Basic_m::get_regencies) -- not a province filter. Kept as `province` here because
     * that's the literal query key the backend controller reads.
     */
    @GET("basic/regencies")
    suspend fun getRegencies(@Query("province") province: String): Response<ApiEnvelope<RegenciesData>>

    /** Only regencies where an active (status=1) vehicle listing exists -- for search filter dropdowns. */
    @GET("basic/active_regencies")
    suspend fun getActiveRegencies(): Response<ApiEnvelope<RegenciesData>>

    // NOTE: the old `getRecomendationRentVehicle()` (POST basic/get_recomendation_rentvehicle)
    // has no equivalent in the new backend -- Basic.php doesn't expose a recommendation
    // endpoint. Promoted/recommended vehicles are now folded into
    // RentVehicleService.listVehicle() (page 1 auto-merges promoted listings). Removed here;
    // update call sites to use RentVehicleService instead.
}
