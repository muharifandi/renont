package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.config
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.RentVehicleConfig
import com.nusatim.sapiriku.domain.usecase.GetPartnerRentVehicleConfigUseCase
import com.nusatim.sapiriku.domain.usecase.UpdatePartnerRentVehicleConfigUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerRentVehicleConfigViewModel @Inject constructor(
    private val getPartnerRentVehicleConfigUseCase: GetPartnerRentVehicleConfigUseCase,
    private val updatePartnerRentVehicleConfigUseCase: UpdatePartnerRentVehicleConfigUseCase
) : ViewModel() {

    private val _config = MutableStateFlow<UiState<RentVehicleConfig>>(UiState.Idle)
    val config = _config.asStateFlow()

    private val _saveState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val saveState = _saveState.asStateFlow()

    fun loadConfig() {
        viewModelScope.launch {
            getPartnerRentVehicleConfigUseCase().collect { resource ->
                _config.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun saveConfig(form: Map<String, String>) {
        viewModelScope.launch {
            updatePartnerRentVehicleConfigUseCase(form).collect { resource ->
                _saveState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
