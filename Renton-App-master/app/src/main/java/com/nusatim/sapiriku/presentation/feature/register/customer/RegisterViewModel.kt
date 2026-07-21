package com.nusatim.sapiriku.presentation.feature.register.customer
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.command.RegisterCustomerCommand
import com.nusatim.sapiriku.domain.usecase.RegisterCustomerUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class RegisterViewModel @Inject constructor(
    private val registerCustomerUseCase: RegisterCustomerUseCase
) : ViewModel() {

    private val _registerState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val registerState = _registerState.asStateFlow()

    fun register(command: RegisterCustomerCommand) {
        viewModelScope.launch {
            registerCustomerUseCase(command).collect { resource ->
                _registerState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
