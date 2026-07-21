package com.rentone.user.presentation.feature.account.historypoint
import android.app.Activity
import android.content.Intent
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import com.rentone.user.R
import com.rentone.user.core.common.UiState
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.custom.PaginationListener
import com.rentone.user.databinding.ActivityCustomerHistoryPointBinding
import com.rentone.user.presentation.feature.account.historypoint.adapter.ListTransactionPointAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition
import com.rentone.user.presentation.feature.account.exchangepoint.CustomerExcangePointActivity

@AndroidEntryPoint
class CustomerHistoryPointActivity : AppCompatActivity() {

    private lateinit var binding: ActivityCustomerHistoryPointBinding
    private val viewModel: CustomerHistoryPointViewModel by viewModels()
    private val adapter = ListTransactionPointAdapter()

    private val activityResultLauncher = registerForActivityResult(
        ActivityResultContracts.StartActivityForResult()
    ) { result ->
        if (result.resultCode == Activity.RESULT_OK) {
            viewModel.loadPoint()
            setResult(Activity.RESULT_OK)
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityCustomerHistoryPointBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.setDisplayHomeAsUpEnabled(true)

        binding.btnExchange.setOnClickListener {
            val intent = Intent(this, CustomerExcangePointActivity::class.java)
            activityResultLauncher.launch(intent)
            applyExitTransition(R.anim.slide_in_right, R.anim.slide_out_left)
        }

        val layoutManager = LinearLayoutManager(this)
        binding.list.layoutManager = layoutManager
        binding.list.adapter = adapter
        binding.list.addOnScrollListener(object : PaginationListener(layoutManager) {
            override fun loadMoreItems() = viewModel.loadMore()
            override fun isLastPage() = viewModel.isLastPage
            override fun isLoading() = viewModel.isLoadingMore
        })
        binding.srLayout.setOnRefreshListener { viewModel.loadFirstPage(isRefresh = true) }

        observeState()
        viewModel.loadPoint()
        viewModel.loadFirstPage()
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.point.collect { state ->
                        if (state is UiState.Success) {
                            binding.txtPoint.text = ViewUtils.formatCurrency(state.data)
                        }
                    }
                }
                launch {
                    viewModel.uiState.collect { state ->
                        binding.srLayout.isRefreshing = state.isRefreshing
                        adapter.setLoading(state.isLoadingMore)
                        adapter.submitList(state.items)

                        val isEmpty = state.items.isEmpty() && !state.isInitialLoading
                        binding.list.isVisible = !isEmpty
                        binding.txtListMessage.isVisible = isEmpty

                        if (state.error != null) {
                            Toast.makeText(this@CustomerHistoryPointActivity, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
                        }
                    }
                }
            }
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
