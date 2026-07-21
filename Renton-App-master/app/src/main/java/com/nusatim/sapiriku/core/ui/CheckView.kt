package com.nusatim.sapiriku.core.ui

import android.animation.ValueAnimator
import android.content.Context
import android.graphics.Canvas
import android.graphics.Paint
import android.graphics.Path
import android.graphics.PathMeasure
import android.graphics.PointF
import android.graphics.RectF
import android.util.AttributeSet
import android.view.View
import android.view.animation.PathInterpolator
import androidx.annotation.ColorInt
import androidx.annotation.FloatRange
import androidx.interpolator.view.animation.FastOutSlowInInterpolator
import com.nusatim.sapiriku.R
import kotlin.math.abs
import kotlin.math.sqrt

private const val CHECK_ANIM_DURATION = 300L
private const val SCALE_ANIM_DELAY = 280L
private const val SCALE_ANIM_DURATION = 250L
private const val DEFAULT_STROKE_WIDTH = 8F
private const val DEFAULT_STROKE_COLOR = 0xFF1AAB00.toInt() // greenish
private const val SCALE_MIN = 0.80F

/** Animating check mark. */
class CheckView @JvmOverloads constructor(
    context: Context,
    attrs: AttributeSet? = null,
    defStyleAttr: Int = 0,
    defStyleRes: Int = 0
) : View(context, attrs, defStyleAttr, defStyleRes) {

    private val checkInterpolator: PathInterpolator = PathInterpolator(0.755F, 0.05F, 0.855F, 0.06F)

    /** The path of the circle around the check mark */
    private val pathCircle = Path()

    /** The path of the check mark */
    private val pathCheck = Path()

    /** The length of the start of the check mark, before the pivot point */
    private var minorContourLength = 0F

    /** The length of the check mark after the pivot point, and up to the end point. */
    private var majorContourLength = 0F

    /** The size of the check mark and circle paths. */
    private var strokeWidth = DEFAULT_STROKE_WIDTH
    private var strokeColor = DEFAULT_STROKE_COLOR

    /** The area on this View's canvas where the check mark should be drawn, accounting for padding. */
    private val drawingRect = RectF()

    /** The drawable area for the circle around the check mark, accounting for the stroke width. */
    private val circleRect = RectF()

    private val paint: Paint
    private val pathMeasure = PathMeasure()

    /** A pre-allocated float array to hold path measure results. */
    private val point = FloatArray(2)

    /** Where the check mark starts */
    private val checkStart = PointF()

    /** Where the check mark turns upward */
    private val checkPivot = PointF()

    /** Where the check mark ends */
    private val checkEnd = PointF()

    /** Where the circle border starts */
    private val circleStart = PointF()

    private val checkAnimator = ValueAnimator.ofFloat(0F, 1F)
    private val circleAnimator = ValueAnimator.ofFloat(0F, 1F)
    private val scaleAnimator = ValueAnimator.ofFloat(1F, SCALE_MIN, 1F)
    private var checked = false

    init {
        resolveAttributes(context, attrs)
        paint = createPaint(strokeColor, strokeWidth)
    }

    private fun resolveAttributes(c: Context, attrs: AttributeSet?) {
        if (attrs == null) return

        val a = c.theme.obtainStyledAttributes(attrs, R.styleable.CheckView, 0, 0)
        try {
            strokeWidth = a.getDimension(R.styleable.CheckView_checkView_strokeWidth, DEFAULT_STROKE_WIDTH)
            strokeColor = a.getColor(R.styleable.CheckView_checkView_strokeColor, DEFAULT_STROKE_COLOR)
        } finally {
            a.recycle()
        }
    }

    override fun onLayout(changed: Boolean, left: Int, top: Int, right: Int, bottom: Int) {
        super.onLayout(changed, left, top, right, bottom)
        if (!changed) return

        drawingRect.left = paddingLeft.toFloat()
        drawingRect.top = paddingTop.toFloat()
        drawingRect.right = (measuredWidth - paddingRight).toFloat()
        drawingRect.bottom = (measuredHeight - paddingBottom).toFloat()

        checkStart.x = drawingRect.left + drawingRect.width() / 4
        checkStart.y = drawingRect.top + drawingRect.height() / 2
        checkPivot.x = drawingRect.left + drawingRect.width() * .426F
        checkPivot.y = drawingRect.top + drawingRect.height() * .66F
        checkEnd.x = drawingRect.left + drawingRect.width() * .75F
        checkEnd.y = drawingRect.top + drawingRect.height() * .30F

        minorContourLength = distance(checkStart.x, checkStart.y, checkPivot.x, checkPivot.y)
        majorContourLength = distance(checkPivot.x, checkPivot.y, checkEnd.x, checkEnd.y)

        circleRect.left = drawingRect.left + strokeWidth / 2
        circleRect.top = drawingRect.top + strokeWidth / 2
        circleRect.right = drawingRect.right - strokeWidth / 2
        circleRect.bottom = drawingRect.bottom - strokeWidth / 2
        circleStart.x = circleRect.right
        circleStart.y = circleRect.bottom / 2
    }

    override fun onDraw(canvas: Canvas) {
        super.onDraw(canvas)
        if (!checked) return
        canvas.drawPath(pathCheck, paint)
        canvas.drawPath(pathCircle, paint)
    }

    /** Tell this [CheckView] to animate into the checked state. */
    fun check() {
        if (checked) return
        checked = true

        checkAnimator.removeAllUpdateListeners()
        checkAnimator.duration = CHECK_ANIM_DURATION
        checkAnimator.interpolator = checkInterpolator
        checkAnimator.addUpdateListener {
            setCheckPathPercentage(it.animatedFraction)
            invalidate()
        }

        circleAnimator.removeAllUpdateListeners()
        circleAnimator.duration = CHECK_ANIM_DURATION
        circleAnimator.interpolator = checkInterpolator
        circleAnimator.addUpdateListener { invalidate() }

        scaleAnimator.removeAllUpdateListeners()
        scaleAnimator.duration = SCALE_ANIM_DURATION
        scaleAnimator.startDelay = SCALE_ANIM_DELAY
        scaleAnimator.interpolator = FastOutSlowInInterpolator()
        scaleAnimator.addUpdateListener {
            val value = it.animatedValue as Float
            scaleX = value
            scaleY = value
            invalidate()
        }

        checkAnimator.start()
        circleAnimator.start()
        scaleAnimator.start()
    }

    /** Reset to an unchecked state. This will not animate. */
    fun uncheck() {
        if (!checked) return
        checked = false
        invalidate()
    }

    private fun createPaint(@ColorInt color: Int, strokeWidth: Float): Paint {
        return Paint().apply {
            this.color = color
            style = Paint.Style.STROKE
            this.strokeWidth = strokeWidth
            strokeJoin = Paint.Join.ROUND
            isAntiAlias = true
            strokeCap = Paint.Cap.ROUND
        }
    }

    /** What does the check mark path look like at [percent] of its total length? */
    private fun setCheckPathPercentage(@FloatRange(from = 0.0, to = 1.0) percent: Float) {
        pathCheck.reset()
        pathCheck.moveTo(checkStart.x, checkStart.y)
        pathCheck.lineTo(checkPivot.x, checkPivot.y)
        pathCheck.lineTo(checkEnd.x, checkEnd.y)

        val totalLength = minorContourLength + majorContourLength
        val pivotPercent = minorContourLength / totalLength

        when {
            percent > pivotPercent -> {
                val remainder = percent - pivotPercent
                val distance = totalLength * remainder
                pathCheck.reset()
                pathCheck.moveTo(checkPivot.x, checkPivot.y)
                pathCheck.lineTo(checkEnd.x, checkEnd.y)
                pathMeasure.setPath(pathCheck, false)
                pathMeasure.getPosTan(distance, point, null)
                pathCheck.reset()
                pathCheck.moveTo(checkStart.x, checkStart.y)
                pathCheck.lineTo(checkPivot.x, checkPivot.y)
                pathCheck.lineTo(point[0], point[1])
            }
            percent < pivotPercent -> {
                val minorPercent = percent / pivotPercent
                val distance = minorContourLength * minorPercent
                pathMeasure.setPath(pathCheck, false)
                pathMeasure.getPosTan(distance, point, null)
                pathCheck.reset()
                pathCheck.moveTo(checkStart.x, checkStart.y)
                pathCheck.lineTo(point[0], point[1])
            }
            else -> pathCheck.lineTo(checkPivot.x, checkPivot.y)
        }
    }

    private fun distance(x1: Float, y1: Float, x2: Float, y2: Float): Float {
        val xAbs = abs(x1 - x2)
        val yAbs = abs(y1 - y2)
        return sqrt((yAbs * yAbs) + (xAbs * xAbs))
    }
}
