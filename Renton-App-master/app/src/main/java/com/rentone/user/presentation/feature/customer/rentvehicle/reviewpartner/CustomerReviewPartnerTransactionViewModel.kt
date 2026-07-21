package com.rentone.user.presentation.feature.customer.rentvehicle.reviewpartner
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.rentone.user.domain.model.OperationResult
import com.rentone.user.core.common.Resource
import com.rentone.user.core.common.UiState
import com.rentone.user.domain.usecase.PostCustomerReviewUseCase
import dagger.hilt.android.lifecycle.HiltViewModel
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import javax.inject.Inject

@HiltViewModel
class CustomerReviewPartnerTransactionViewModel @Inject constructor(
    private val postCustomerReviewUseCase: PostCustomerReviewUseCase
) : ViewModel() {

    private val _postState = MutableStateFlow<UiState<OperationResult>>(UiState.Idle)
    val postState = _postState.asStateFlow()

    fun postReview(transactionId: Int, rating: Float, comment: String) {
        viewModelScope.launch {
            postCustomerReviewUseCase(transactionId, rating, comment).collect { resource ->
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
