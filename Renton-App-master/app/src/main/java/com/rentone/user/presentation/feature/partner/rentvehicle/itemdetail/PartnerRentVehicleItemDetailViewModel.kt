package com.rentone.user.presentation.feature.partner.rentvehicle.itemdetail
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.model.Vehicle
import com.rentone.user.domain.usecase.DeletePartnerVehicleUseCase
import com.rentone.user.domain.usecase.GetPartnerVehicleDetailUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerRentVehicleItemDetailViewModel @Inject constructor(
    private val getPartnerVehicleDetailUseCase: GetPartnerVehicleDetailUseCase,
    private val deletePartnerVehicleUseCase: DeletePartnerVehicleUseCase
) : ViewModel() {

    private val _detail = MutableStateFlow<UiState<Vehicle>>(UiState.Idle)
    val detail = _detail.asStateFlow()

    private val _deleteState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val deleteState = _deleteState.asStateFlow()

    fun loadDetail(id: Int) {
        viewModelScope.launch {
            getPartnerVehicleDetailUseCase(id).collect { resource ->
                _detail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun deleteVehicle(id: Int) {
        viewModelScope.launch {
            deletePartnerVehicleUseCase(id).collect { resource ->
                _deleteState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
