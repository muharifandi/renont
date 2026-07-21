package com.rentone.user.presentation.feature.account.historybalance
import com.rentone.user.core.ui.PagedListViewModel
import com.rentone.user.domain.model.Topup
import com.rentone.user.domain.usecase.ListCustomerTopupUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel
class CustomerTopupViewModel @Inject constructor(
    private val listCustomerTopupUseCase: ListCustomerTopupUseCase
) : PagedListViewModel<Topup>() {

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<Topup>> =
        listCustomerTopupUseCase(page, pageSize)
}
