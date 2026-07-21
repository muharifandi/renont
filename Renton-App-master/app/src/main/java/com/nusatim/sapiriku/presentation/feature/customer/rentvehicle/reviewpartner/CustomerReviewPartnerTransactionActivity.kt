package com.nusatim.sapiriku.presentation.feature.customer.rentvehicle.reviewpartner
import android.app.Activity
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.nusatim.sapiriku.R
import com.nusatim.sapiriku.domain.model.OperationResult
import com.nusatim.sapiriku.core.common.UiState
import com.nusatim.sapiriku.databinding.ActivityCustomerReviewPartnerTransactionBinding
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.nusatim.sapiriku.core.util.applyExitTransition

@AndroidEntryPoint
class CustomerReviewPartnerTransactionActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerReviewPartnerTransactionBinding
    private val viewModel: CustomerReviewPartnerTransactionViewModel by viewModels()

    private var transactionId = 0

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerReviewPartnerTransactionBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        transactionId = intent.getIntExtra("transaction_id", 0)

        binding.btnPost.setOnClickListener {
            viewModel.postReview(transactionId, binding.ratingBar.rating, binding.inputComment.text.toString())
        }

        observeState()
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.postState.collect { state -> handlePostState(state) }
            }
        }
    }

    private fun handlePostState(state: UiState<OperationResult>) {
        binding.btnPost.isEnabled = state !is UiState.Loading

        when (state) {
            is UiState.Success -> {
                AlertDialog.Builder(this)
                    .setTitle(getString(R.string.title_customer_review_partner_transaction))
                    .setMessage(state.data.message)
                    .setPositiveButton(R.string.yes) { _, _ ->
                        if (state.data.success) {
                            setResult(Activity.RESULT_OK)
                            finish()
                        }
                    }
                    .show()
            }
            is UiState.Error -> {
                Toast.makeText(this, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
            }
            else -> Unit
        }
    }

    override fun onOptionsItemSelected(item: MenuItem): Boolean {
        if (item.itemId == android.R.id.home) {
            onBackPressedDispatcher.onBackPressed()
        }
        return true
    }

    override fun finish() {
        super.finish()
        applyExitTransition(R.anim.slide_in_left, R.anim.slide_out_right)
    }
}
