package com.nusatim.sapiriku.presentation.feature.partner.changeaddress
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.usecase.ChangePartnerAddressUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerChangeAddressViewModel @Inject constructor(
    private val changePartnerAddressUseCase: ChangePartnerAddressUseCase
) : ViewModel() {

    private val _state = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val state = _state.asStateFlow()

    fun changeAddress(address: String) {
        viewModelScope.launch {
            changePartnerAddressUseCase(address).collect { resource ->
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
