package com.rentone.user.presentation.feature.account.verificationtopup

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.Topup
import com.rentone.user.domain.model.command.UploadImageCommand
import com.rentone.user.domain.usecase.GetTopupDetailUseCase
import com.rentone.user.domain.usecase.PostVerificationTopupUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerVerificationTopupViewModel @Inject constructor(
    private val getTopupDetailUseCase: GetTopupDetailUseCase,
    private val postVerificationTopupUseCase: PostVerificationTopupUseCase
) : ViewModel() {

    private val _topupDetail = MutableStateFlow<UiState<Topup?>>(UiState.Idle)
    val topupDetail = _topupDetail.asStateFlow()

    private val _verifyStatus = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val verifyStatus = _verifyStatus.asStateFlow()

    fun getDetail(topupId: Int) {
        viewModelScope.launch {
            getTopupDetailUseCase(topupId).collect { resource ->
                _topupDetail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun verify(topupId: Int, imagePath: String) {
        viewModelScope.launch {
            val command = UploadImageCommand(imagePath)
            postVerificationTopupUseCase(topupId, command).collect { resource ->
                _verifyStatus.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
