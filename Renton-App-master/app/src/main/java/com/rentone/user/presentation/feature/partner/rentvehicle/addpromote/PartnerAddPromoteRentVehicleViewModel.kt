package com.rentone.user.presentation.feature.partner.rentvehicle.addpromote
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.InputPromoteRentVehicleConfig
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.usecase.GetPartnerPromoteInputConfigUseCase
import com.rentone.user.domain.usecase.PostPartnerPromoteUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerAddPromoteRentVehicleViewModel @Inject constructor(
    private val getPartnerPromoteInputConfigUseCase: GetPartnerPromoteInputConfigUseCase,
    private val postPartnerPromoteUseCase: PostPartnerPromoteUseCase
) : ViewModel() {

    private val _config = MutableStateFlow<UiState<InputPromoteRentVehicleConfig>>(UiState.Idle)
    val config = _config.asStateFlow()

    private val _saveState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val saveState = _saveState.asStateFlow()

    fun loadConfig() {
        viewModelScope.launch {
            getPartnerPromoteInputConfigUseCase().collect { resource ->
                _config.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun postPromote(itemId: Int, startDate: String, endDate: String) {
        viewModelScope.launch {
            postPartnerPromoteUseCase(itemId, startDate, endDate).collect { resource ->
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
