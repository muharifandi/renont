package com.rentone.user.presentation.feature.rentvehicle.listreview
import com.rentone.user.core.ui.PagedListViewModel
import com.rentone.user.domain.model.Review
import com.rentone.user.domain.usecase.ListVehicleReviewsUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import javax.inject.Inject

@HiltViewModel
class RentVehicleListReviewViewModel @Inject constructor(
    private val listVehicleReviewsUseCase: ListVehicleReviewsUseCase
) : PagedListViewModel<Review>() {

    var vehicleId: Int = 0

    private val _reviewTotal = MutableStateFlow(0)
    val reviewTotal = _reviewTotal.asStateFlow()

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<Review>> {
        val result = listVehicleReviewsUseCase(vehicleId, page, pageSize)
        return result.map { 
            _reviewTotal.value = it.reviewTotal
            it.reviews 
        }
    }
}
