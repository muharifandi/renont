package com.rentone.user.presentation.feature.filter

import androidx.lifecycle.SavedStateHandle
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.model.FilterList
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import javax.inject.Inject

@HiltViewModel
class ListFilterViewModel @Inject constructor(
    private val savedStateHandle: SavedStateHandle
) : ViewModel() {

    private val _filterState = MutableStateFlow<UiState<FilterList>>(UiState.Loading)
    val filterState = _filterState.asStateFlow()

    private var currentFilter = FilterList()

    init {
        loadInitialData()
    }

    private fun loadInitialData() {
        val filter = savedStateHandle.get<FilterList>("filter")
        if (filter != null) {
            currentFilter = filter
            _filterState.value = UiState.Success(filter)
        } else {
            _filterState.value = UiState.Error("Filter data not found")
        }
    }

    fun updateStatus(status: Int) {
        currentFilter = currentFilter.copy(status = status)
        updateState()
    }

    fun updatePassengerRange(min: Int, max: Int) {
        currentFilter = currentFilter.copy(minPassenger = min, maxPassenger = max)
        updateState()
    }

    fun updatePriceRange(min: Double, max: Double) {
        currentFilter = currentFilter.copy(minPrice = min, maxPrice = max)
        updateState()
    }

    fun toggleFunctionalType(id: String) {
        val currentSelected = currentFilter.vehicleFunctionalTypeIdSelected.toMutableList()
        if (currentSelected.contains(id)) {
            currentSelected.remove(id)
        } else {
            currentSelected.add(id)
        }
        currentFilter = currentFilter.copy(vehicleFunctionalTypeIdSelected = currentSelected)
        updateState()
    }

    fun resetFilters() {
        currentFilter = currentFilter.copy(
            status = -1,
            minPassenger = -1,
            maxPassenger = -1,
            minPrice = currentFilter.priceMin,
            maxPrice = currentFilter.priceMax,
            vehicleFunctionalTypeIdSelected = emptyList()
        )
        updateState()
    }

    private fun updateState() {
        _filterState.update { UiState.Success(currentFilter) }
    }

    fun getFinalFilter() = currentFilter
}
