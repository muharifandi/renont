package com.nusatim.sapiriku.presentation.feature.rentvehicle.selectregency
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.BasicData
import com.nusatim.sapiriku.domain.usecase.GetActiveRegenciesUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class RentVehicleSelectRegencyViewModel @Inject constructor(
    private val getActiveRegenciesUseCase: GetActiveRegenciesUseCase
) : ViewModel() {

    private val _regencies = MutableStateFlow<UiState<List<BasicData>>>(UiState.Idle)
    val regencies = _regencies.asStateFlow()

    fun loadRegencies() {
        viewModelScope.launch {
            getActiveRegenciesUseCase().collect { resource ->
                _regencies.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
