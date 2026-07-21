package com.rentone.user.presentation.feature.account.verificationtopup
import android.net.Uri
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.Topup
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
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

    private val _detail = MutableStateFlow<UiState<Topup?>>(UiState.Idle)
    val detail = _detail.asStateFlow()

    private val _verificationState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val verificationState = _verificationState.asStateFlow()

    fun loadDetail(topupId: Int) {
        viewModelScope.launch {
            getTopupDetailUseCase(topupId).collect { resource ->
                _detail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun verify(topupId: Int, proofImageUri: Uri) {
        viewModelScope.launch {
            postVerificationTopupUseCase(topupId, proofImageUri).collect { resource ->
                _verificationState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
