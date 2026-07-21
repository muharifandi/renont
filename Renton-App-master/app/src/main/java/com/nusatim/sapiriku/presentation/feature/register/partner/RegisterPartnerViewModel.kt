package com.nusatim.sapiriku.presentation.feature.register.partner
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.command.RegisterPartnerCommand
import com.nusatim.sapiriku.domain.usecase.RegisterPartnerUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class RegisterPartnerViewModel @Inject constructor(
    private val registerPartnerUseCase: RegisterPartnerUseCase
) : ViewModel() {

    private val _registerState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val registerState = _registerState.asStateFlow()

    fun register(command: RegisterPartnerCommand) {
        viewModelScope.launch {
            registerPartnerUseCase(command).collect { resource ->
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
