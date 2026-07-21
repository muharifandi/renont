package com.nusatim.sapiriku.presentation.feature.account.historybalance
import com.nusatim.sapiriku.core.ui.PagedListViewModel
import com.nusatim.sapiriku.domain.model.Topup
import com.nusatim.sapiriku.domain.usecase.ListCustomerTopupUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel
class CustomerTopupViewModel @Inject constructor(
    private val listCustomerTopupUseCase: ListCustomerTopupUseCase
) : PagedListViewModel<Topup>() {

    override suspend fun fetchPage(page: Int, pageSize: Int): Result<List<Topup>> =
        listCustomerTopupUseCase(page, pageSize)
}
