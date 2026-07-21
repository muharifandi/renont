package com.nusatim.sapiriku.presentation.feature.partner.rentvehicle.reviewcustomer
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.domain.usecase.PostPartnerReviewUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class PartnerReviewCustomerTransactionViewModel @Inject constructor(
    private val postPartnerReviewUseCase: PostPartnerReviewUseCase
) : ViewModel() {

    private val _postState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val postState = _postState.asStateFlow()

    fun postReview(transactionId: Int, rating: Float, comment: String) {
        viewModelScope.launch {
            postPartnerReviewUseCase(transactionId, rating, comment).collect { resource ->
                _postState.value = when (resource) {
                    is Resource.Loading -> UiState.Loading
                    is Resource.Success -> UiState.Success(resource.data)
                    is Resource.Error -> UiState.Error(resource.message)
                    is Resource.Empty -> UiState.Empty
                }
            }
        }
    }
}
