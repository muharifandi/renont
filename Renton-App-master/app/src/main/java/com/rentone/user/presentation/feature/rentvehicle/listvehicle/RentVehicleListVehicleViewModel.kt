package com.rentone.user.presentation.feature.rentvehicle.listvehicle
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.domain.model.FilterList
import com.rentone.user.domain.model.Vehicle
import com.rentone.user.domain.usecase.ListRentVehiclesUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.Job
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

data class RentVehicleListUiState(
    val vehicles: List<Vehicle> = emptyList(),
    val isInitialLoading: Boolean = false,
    val isLoadingMore: Boolean = false,
    val isRefreshing: Boolean = false,
    val isLastPage: Boolean = false,
    val error: String? = null,
    val filterList: FilterList = FilterList()
)

@HiltViewModel
class RentVehicleListVehicleViewModel @Inject constructor(
    private val listRentVehiclesUseCase: ListRentVehiclesUseCase
) : ViewModel() {

    private val _uiState = MutableStateFlow(RentVehicleListUiState())
    val uiState = _uiState.asStateFlow()

    var param: HashMap<String, String> = HashMap()
    var sortIndex: Int = 0

    private var currentPage = 1
    private var loadJob: Job? = null
    private val pageSize = 10

    private fun buildParams(page: Int): Map<String, String> {
        val filter = _uiState.value.filterList
        return buildMap {
            putAll(param)
            putAll(filter.toQueryMap())
            put("page", page.toString())
            put("limit", pageSize.toString())
            put("sort", sortIndex.toString())
        }
    }

    fun loadFirstPage(isRefresh: Boolean = false) {
        loadJob?.cancel()
        currentPage = 1
        loadJob = viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isInitialLoading = !isRefresh, isRefreshing = isRefresh, error = null)
            val result = listRentVehiclesUseCase(buildParams(currentPage))
            _uiState.value = result.fold(
                onSuccess = { body ->
                    _uiState.value.copy(
                        vehicles = body.vehicles,
                        isInitialLoading = false,
                        isRefreshing = false,
                        isLastPage = body.vehicles.size < pageSize,
                        filterList = _uiState.value.filterList.copy(
                            vehicleFunctionalType = body.functionalType,
                            priceMin = body.priceMin,
                            priceMax = body.priceMax
                        ),
                        error = null
                    )
                },
                onFailure = { e -> _uiState.value.copy(isInitialLoading = false, isRefreshing = false, error = e.message) }
            )
        }
    }

    fun loadMore() {
        val state = _uiState.value
        if (state.isLoadingMore || state.isLastPage || state.isInitialLoading) return

        loadJob = viewModelScope.launch {
            _uiState.value = _uiState.value.copy(isLoadingMore = true)
            val nextPage = currentPage + 1
            val result = listRentVehiclesUseCase(buildParams(nextPage))
            _uiState.value = result.fold(
                onSuccess = { body ->
                    if (body.vehicles.isEmpty()) {
                        _uiState.value.copy(isLoadingMore = false, isLastPage = true)
                    } else {
                        currentPage = nextPage
                        _uiState.value.copy(
                            vehicles = _uiState.value.vehicles + body.vehicles,
                            isLoadingMore = false,
                            isLastPage = body.vehicles.size < pageSize
                        )
                    }
                },
                onFailure = { _uiState.value.copy(isLoadingMore = false) }
            )
        }
    }

    fun updateSort(sortIndex: Int) {
        this.sortIndex = sortIndex
        loadFirstPage()
    }

    fun updateFilter(filterList: FilterList) {
        _uiState.value = _uiState.value.copy(filterList = filterList)
        loadFirstPage()
    }
}
