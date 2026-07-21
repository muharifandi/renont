package com.nusatim.sapiriku.presentation.feature.account.historybalance
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.usecase.GetCustomerBalanceUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerHistoryBalanceViewModel @Inject constructor(
    private val getCustomerBalanceUseCase: GetCustomerBalanceUseCase
) : ViewModel() {

    private val _balance = MutableStateFlow<UiState<Double>>(UiState.Idle)
    val balance = _balance.asStateFlow()

    fun loadBalance() {
        viewModelScope.launch {
            getCustomerBalanceUseCase().collect { resource ->
                _balance.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
