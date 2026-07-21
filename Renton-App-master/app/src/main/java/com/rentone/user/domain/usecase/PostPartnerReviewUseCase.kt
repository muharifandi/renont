package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerTransactionRepository
import javax.inject.Inject

class PostPartnerReviewUseCase @Inject constructor(
    private val partnerTransactionRepository: PartnerTransactionRepository
) {
    operator fun invoke(transactionId: Int, rating: Float, comment: String) =
        partnerTransactionRepository.postReview(transactionId, rating, comment)
}
