package com.nusatim.sapiriku.presentation.feature.account.historypoint
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.core.ui.PagedListViewModel
import com.nusatim.sapiriku.domain.model.TransactionPoint
import com.nusatim.sapiriku.domain.usecase.GetCustomerPointUseCase
import com.nusatim.sapiriku.domain.usecase.ListCustomerTransactionPointUseCase
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
