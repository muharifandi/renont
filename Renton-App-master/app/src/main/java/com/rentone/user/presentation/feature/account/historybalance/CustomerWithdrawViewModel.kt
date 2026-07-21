package com.rentone.user.presentation.feature.account.historybalance
import com.rentone.user.core.ui.PagedListViewModel
import com.rentone.user.domain.model.Withdraw
import com.rentone.user.domain.usecase.ListCustomerWithdrawUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel
class CustomerWithdrawViewModel @Inject constructor(
    private val listCustomerWithdrawUseCase: ListCustomerWithdrawUseCase
) : PagedListViewModel<Withdraw>() {

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<Withdraw>> =
        listCustomerWithdrawUseCase(page, pageSize)
}
