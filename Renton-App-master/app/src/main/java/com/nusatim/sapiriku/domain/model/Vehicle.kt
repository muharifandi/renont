package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.InternalSerializationApi
import kotlinx.serialization.Serializable


@Serializable
@OptIn(InternalSerializationApi::class)
data class Vehicle(
    val id: Int = 0,
    val promote: Int = 0,
    val rating: Double = 0.0,
    val distance: Double = 0.0,
    val totalReview: Int = 0,
    val regenciesId: Int = 0,
    val regenciesName: String? = null,
    val functionalType: Int = -1,
    val functionalName: String? = null,
    val functionalTypeIcon: String? = null,
    val vehicleType: Int = -1,
    val vehicleTypeName: String? = null,
    val vehicleTypeIcon: String? = null,
    val img: String? = null,
    val photos: List<VehicleItemImage> = emptyList(),
    val title: String? = null,
    val brandId: Int = -1,
    val brandName: String? = null,
    val brandIcon: String? = null,
    val vehicleModel: Int = -1,
    val vehicleModelName: String? = null,
    val vehicleModelIcon: String? = null,
    val year: Int = 0,
    val colorId: Int = -1,
    val colorName: String? = null,
    val colorValue: String? = null,
    val maxPassenger: Int = 0,
    val maxBaggage: Int = 0,
    val drivenType: Int = -1,
    val drivenTypeName: String? = null,
    val drivenTypeIcon: String? = null,
    val transmitionType: Int = -1,
    val transmitionTypeName: String? = null,
    val transmitionTypeIcon: String? = null,
    val fuelType: Int = -1,
    val fuelTypeName: String? = null,
    val fuelTypeIcon: String? = null,
    val price: Double = 0.0,
    val priceWithDriverBasic: Double = 0.0,
    val priceWithDriverFull: Double = 0.0,
    val withDriver: Int = 0,
    val delivered: Int = 0,
    val pickoff: Int = 0,
    val status: Int = 0,
    val statusName: String? = null
)


@Serializable
@OptIn(InternalSerializationApi::class)
data class VehicleItemImage(
    val id: Int = 0,
    val itemId: Int = 0,
    val img: String? = null
)
