package com.rentone.user.domain.model

import java.io.Serializable as JavaSerializable


data class FilterList(
    var status: Int = -1,
    var minPassenger: Int = -1,
    var maxPassenger: Int = -1,
    var minPrice: Double = -1.0,
    var maxPrice: Double = -1.0,
    var priceMin: Double = 0.0,
    var priceMax: Double = 0.0,
    var vehicleFunctionalType: List<BasicData> = emptyList(),
    var vehicleFunctionalTypeIdSelected: List<String> = emptyList()
) : JavaSerializable {
    fun toQueryMap(): Map<String, String> {
        return buildMap {
            if (status != -1) put("status", status.toString())
            if (minPassenger != -1) put("min_passenger", minPassenger.toString())
            if (maxPassenger != -1) put("max_passenger", maxPassenger.toString())
            if (minPrice != -1.0) put("min_price", minPrice.toInt().toString())
            if (maxPrice != -1.0) put("max_price", maxPrice.toInt().toString())
            if (vehicleFunctionalTypeIdSelected.isNotEmpty()) {
                put("vehicle_functional_type_selected", vehicleFunctionalTypeIdSelected.joinToString(","))
            }
        }
    }
}
