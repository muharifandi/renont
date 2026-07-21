package com.rentone.user.presentation.feature.account.historypoint
import androidx.lifecycle.viewModelScope
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.core.ui.PagedListViewModel
import com.rentone.user.domain.model.TransactionPoint
import com.rentone.user.domain.usecase.GetCustomerPointUseCase
import com.rentone.user.domain.usecase.ListCustomerTransactionPointUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerHistoryPointViewModel @Inject constructor(
    private val getCustomerPointUseCase: GetCustomerPointUseCase,
    private val listCustomerTransactionPointUseCase: ListCustomerTransactionPointUseCase
) : PagedListViewModel<TransactionPoint>() {

    private val _point = MutableStateFlow<UiState<Double>>(UiState.Idle)
    val point = _point.asStateFlow()

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<TransactionPoint>> =
        listCustomerTransactionPointUseCase(page, pageSize)

    fun loadPoint() {
        viewModelScope.launch {
            getCustomerPointUseCase().collect { resource ->
                _point.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
