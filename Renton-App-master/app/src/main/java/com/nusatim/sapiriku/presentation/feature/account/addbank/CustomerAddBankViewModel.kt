package com.nusatim.sapiriku.presentation.feature.account.addbank
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.Bank
import com.nusatim.sapiriku.domain.model.CustomerBank
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.domain.model.command.AddBankCommand
import com.nusatim.sapiriku.domain.usecase.GetBankInputConfigUseCase
import com.nusatim.sapiriku.domain.usecase.GetCustomerBankDetailUseCase
import com.nusatim.sapiriku.domain.usecase.PostCustomerBankUseCase
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
