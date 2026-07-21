package com.nusatim.sapiriku.presentation.feature.account.requestwithdraw

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.RequestWithdrawConfig
import com.nusatim.sapiriku.domain.model.command.WithdrawRequestCommand
import com.nusatim.sapiriku.domain.usecase.GetRequestWithdrawConfigUseCase
import com.nusatim.sapiriku.domain.usecase.PostRequestWithdrawUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerRequestWithdrawViewModel @Inject constructor(
    private val getRequestWithdrawConfigUseCase: GetRequestWithdrawConfigUseCase,
    private val postRequestWithdrawUseCase: PostRequestWithdrawUseCase
) : ViewModel() {

    private val _config = MutableStateFlow<UiState<RequestWithdrawConfig>>(UiState.Idle)
    val config = _config.asStateFlow()

    private val _requestStatus = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val requestStatus = _requestStatus.asStateFlow()

    fun loadConfig() {
        viewModelScope.launch {
            getRequestWithdrawConfigUseCase().collect { resource ->
                _config.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun requestWithdraw(accountBankId: Int, amount: String) {
        viewModelScope.launch {
            val command = WithdrawRequestCommand(accountBankId, amount)
            postRequestWithdrawUseCase(command).collect { resource ->
                _requestStatus.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
