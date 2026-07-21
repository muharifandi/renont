package com.nusatim.sapiriku.presentation.feature.account

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.CustomerAccountDetail
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import com.nusatim.sapiriku.domain.repository.SessionRepository
import com.nusatim.sapiriku.domain.usecase.GetCustomerDetailUseCase
import com.nusatim.sapiriku.domain.usecase.UploadCustomerProfileImageUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class AccountViewModel @Inject constructor(
    private val getCustomerDetailUseCase: GetCustomerDetailUseCase,
    private val uploadCustomerProfileImageUseCase: UploadCustomerProfileImageUseCase,
    private val sessionRepository: SessionRepository
) : ViewModel() {

    fun logout() {
        viewModelScope.launch { sessionRepository.clearSession() }
    }

    private val _customerDetail = MutableStateFlow<UiState<CustomerAccountDetail>>(UiState.Idle)
    val customerDetail = _customerDetail.asStateFlow()

    private val _uploadStatus = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val uploadStatus = _uploadStatus.asStateFlow()

    fun getCustomerDetail() {
        viewModelScope.launch {
            getCustomerDetailUseCase().collect { resource ->
                _customerDetail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun uploadProfileImage(imagePath: String) {
        viewModelScope.launch {
            uploadCustomerProfileImageUseCase(UploadImageCommand(imagePath)).collect { resource ->
                _uploadStatus.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> {
                        getCustomerDetail() // Refresh
                        UiState.Success(resource.data)
                    }
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
