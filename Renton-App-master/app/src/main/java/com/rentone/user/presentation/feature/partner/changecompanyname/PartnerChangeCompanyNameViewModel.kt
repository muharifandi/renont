package com.rentone.user.presentation.feature.partner.changecompanyname
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.usecase.ChangePartnerCompanyNameUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerChangeCompanyNameViewModel @Inject constructor(
    private val changePartnerCompanyNameUseCase: ChangePartnerCompanyNameUseCase
) : ViewModel() {

    private val _state = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val state = _state.asStateFlow()

    fun changeCompanyName(companyName: String) {
        viewModelScope.launch {
            changePartnerCompanyNameUseCase(companyName).collect { resource ->
                _state.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
