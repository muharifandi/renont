package com.nusatim.sapiriku.presentation.feature.account.historybalance
import com.nusatim.sapiriku.core.ui.PagedListViewModel
import com.nusatim.sapiriku.domain.model.Withdraw
import com.nusatim.sapiriku.domain.usecase.ListCustomerWithdrawUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel
class CustomerWithdrawViewModel @Inject constructor(
    private val listCustomerWithdrawUseCase: ListCustomerWithdrawUseCase
) : PagedListViewModel<Withdraw>() {

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<Withdraw>> =
        listCustomerWithdrawUseCase(page, pageSize)
}
