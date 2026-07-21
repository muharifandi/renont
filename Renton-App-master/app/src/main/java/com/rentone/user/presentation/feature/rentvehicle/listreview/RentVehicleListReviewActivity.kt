package com.rentone.user.presentation.feature.rentvehicle.listreview
import android.os.Bundle
import android.view.MenuItem
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.isVisible
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.recyclerview.widget.LinearLayoutManager
import com.rentone.user.R
import com.rentone.user.custom.PaginationListener
import com.rentone.user.databinding.ActivityRentVehicleListReviewBinding
import com.rentone.user.presentation.feature.rentvehicle.adapter.ListVehicleReviewAdapter
import dagger.hilt.android.AndroidEntryPoint
import kotlinx.coroutines.launch
import com.rentone.user.core.util.applyExitTransition

@AndroidEntryPoint
class RentVehicleListReviewActivity : AppCompatActivity() {

    private lateinit var binding: ActivityRentVehicleListReviewBinding
    private val viewModel: RentVehicleListReviewViewModel by viewModels()
    private val adapter = ListVehicleReviewAdapter()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityRentVehicleListReviewBinding.inflate(layoutInflater)
        setContentView(binding.root)

        supportActionBar?.apply {
            setDisplayHomeAsUpEnabled(true)
            elevation = 0f
        }

        viewModel.vehicleId = intent.getIntExtra("id", 0)

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
        viewModel.loadFirstPage()
    }

    private fun observeState() {
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                launch {
                    viewModel.reviewTotal.collect { total ->
                        title = "${getString(R.string.title_list_review)} ($total)"
                    }
                }
                launch {
                    viewModel.uiState.collect { state ->
                        binding.srLayout.isRefreshing = state.isRefreshing
                        adapter.setLoading(state.isLoadingMore)
                        adapter.submitList(state.items)

                        if (state.isInitialLoading) {
                            binding.shimmer.isVisible = true
                            binding.shimmer.startShimmer()
                        } else {
                            binding.shimmer.stopShimmer()
                            binding.shimmer.isVisible = false
                        }

                        val isEmpty = state.items.isEmpty() && !state.isInitialLoading
                        binding.list.isVisible = !isEmpty
                        binding.txtListMessage.isVisible = isEmpty

                        if (state.error != null) {
                            Toast.makeText(this@RentVehicleListReviewActivity, getString(R.string.failed_check_to_server), Toast.LENGTH_LONG).show()
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
