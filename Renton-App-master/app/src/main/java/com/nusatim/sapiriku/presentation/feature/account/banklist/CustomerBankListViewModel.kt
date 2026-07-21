package com.nusatim.sapiriku.presentation.feature.account.banklist
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.model.CustomerBank
import com.nusatim.sapiriku.domain.usecase.DeleteCustomerBankUseCase
import com.nusatim.sapiriku.domain.usecase.ListCustomerBanksUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerBankListViewModel @Inject constructor(
    private val listCustomerBanksUseCase: ListCustomerBanksUseCase,
    private val deleteCustomerBankUseCase: DeleteCustomerBankUseCase
) : ViewModel() {

    private val _banks = MutableStateFlow<UiState<List<CustomerBank>>>(UiState.Idle)
    val banks = _banks.asStateFlow()

    private val _deleteState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val deleteState = _deleteState.asStateFlow()

    fun loadBanks() {
        viewModelScope.launch {
            listCustomerBanksUseCase().collect { resource ->
                _banks.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }

    fun deleteBank(id: Int) {
        viewModelScope.launch {
            deleteCustomerBankUseCase(id).collect { resource ->
                _deleteState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
