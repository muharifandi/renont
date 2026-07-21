package com.rentone.user.presentation.feature.register.partner
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.usecase.RegisterPartnerUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import okhttp3.MultipartBody
import okhttp3.RequestBody
import javax.inject.Inject

@HiltViewModel
class RegisterPartnerViewModel @Inject constructor(
    private val registerPartnerUseCase: RegisterPartnerUseCase
) : ViewModel() {

    private val _registerState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val registerState = _registerState.asStateFlow()

    fun register(form: Map<String, RequestBody>, files: List<MultipartBody.Part>) {
        viewModelScope.launch {
            registerPartnerUseCase(form, files).collect { resource ->
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
