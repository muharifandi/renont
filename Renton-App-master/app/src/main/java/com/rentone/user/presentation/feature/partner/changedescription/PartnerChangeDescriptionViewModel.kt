package com.rentone.user.presentation.feature.partner.changedescription
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.usecase.ChangePartnerDescriptionUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerChangeDescriptionViewModel @Inject constructor(
    private val changePartnerDescriptionUseCase: ChangePartnerDescriptionUseCase
) : ViewModel() {

    private val _state = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val state = _state.asStateFlow()

    fun changeDescription(description: String) {
        viewModelScope.launch {
            changePartnerDescriptionUseCase(description).collect { resource ->
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
