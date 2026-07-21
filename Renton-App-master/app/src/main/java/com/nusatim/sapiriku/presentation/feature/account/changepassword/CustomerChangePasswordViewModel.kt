package com.nusatim.sapiriku.presentation.feature.account.changepassword
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.usecase.ChangeCustomerPasswordUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerChangePasswordViewModel @Inject constructor(
    private val changeCustomerPasswordUseCase: ChangeCustomerPasswordUseCase
) : ViewModel() {

    private val _state = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val state = _state.asStateFlow()

    fun changePassword(oldPassword: String, newPassword: String) {
        viewModelScope.launch {
            changeCustomerPasswordUseCase(oldPassword, newPassword).collect { resource ->
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
