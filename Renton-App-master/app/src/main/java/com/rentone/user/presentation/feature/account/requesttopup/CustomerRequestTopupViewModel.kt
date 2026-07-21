package com.rentone.user.presentation.feature.account.requesttopup

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.model.RequestTopupConfig
import com.rentone.user.domain.model.TopupRequestResult
import com.rentone.user.domain.model.command.TopupRequestCommand
import com.rentone.user.domain.usecase.GetRequestTopupConfigUseCase
import com.rentone.user.domain.usecase.PostRequestTopupUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerRequestTopupViewModel @Inject constructor(
    private val getRequestTopupConfigUseCase: GetRequestTopupConfigUseCase,
    private val postRequestTopupUseCase: PostRequestTopupUseCase
) : ViewModel() {

    private val _config = MutableStateFlow<UiState<RequestTopupConfig>>(UiState.Idle)
    val config = _config.asStateFlow()

    private val _requestStatus = MutableStateFlow<UiState<TopupRequestResult>>(UiState.Idle)
    val requestStatus = _requestStatus.asStateFlow()

    fun loadConfig() {
        viewModelScope.launch {
            getRequestTopupConfigUseCase().collect { resource ->
                _config.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun requestTopup(companyBankId: Int, amount: String) {
        viewModelScope.launch {
            val command = TopupRequestCommand(companyBankId, amount)
            postRequestTopupUseCase(command).collect { resource ->
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
