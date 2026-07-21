package com.rentone.user.presentation.feature.account.addbank
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.model.Bank
import com.rentone.user.domain.model.CustomerBank
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.domain.model.command.AddBankCommand
import com.rentone.user.domain.usecase.GetBankInputConfigUseCase
import com.rentone.user.domain.usecase.GetCustomerBankDetailUseCase
import com.rentone.user.domain.usecase.PostCustomerBankUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerAddBankViewModel @Inject constructor(
    private val getBankInputConfigUseCase: GetBankInputConfigUseCase,
    private val getCustomerBankDetailUseCase: GetCustomerBankDetailUseCase,
    private val postCustomerBankUseCase: PostCustomerBankUseCase
) : ViewModel() {

    private val _config = MutableStateFlow<UiState<List<Bank>>>(UiState.Idle)
    val config = _config.asStateFlow()

    private val _bankDetail = MutableStateFlow<UiState<CustomerBank?>>(UiState.Idle)
    val bankDetail = _bankDetail.asStateFlow()

    private val _saveState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val saveState = _saveState.asStateFlow()

    fun loadConfig() {
        viewModelScope.launch {
            getBankInputConfigUseCase().collect { resource ->
                _config.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun loadBankDetail(id: Int) {
        viewModelScope.launch {
            getCustomerBankDetailUseCase(id).collect { resource ->
                _bankDetail.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun saveBank(id: Int?, bankId: Int, name: String, bankNumber: String) {
        viewModelScope.launch {
            val command = AddBankCommand(id, bankId, name, bankNumber)
            postCustomerBankUseCase(command).collect { resource ->
                _saveState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
